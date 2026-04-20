<?php

namespace App\Services;

use App\Enums\OdemeKalemiTipi;
use App\Enums\TahsilatOnayDurumu;
use App\Models\Protokol;
use App\Models\ProtokolTaksit;
use App\Models\Tahsilat;
use App\Models\TahsilatDekontu;
use App\Models\User;
use App\Support\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TahsilatService
{
    public function __construct(
        private readonly ProtokolService $protokolService,
    ) {
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        $perPage = max(1, min((int) ($filters['per_page'] ?? 20), 100));

        $query = Tahsilat::query()
            ->with([
                'muvekkil',
                'protokol.portfoy',
                'dekontlar',
            ])
            ->when(! empty($filters['q']), function (Builder $query) use ($filters) {
                $needle = trim((string) $filters['q']);

                $query->where(function (Builder $sub) use ($needle) {
                    $sub->where('borclu_adi', 'like', '%'.$needle.'%')
                        ->orWhere('borclu_tckn_vkn', 'like', '%'.$needle.'%')
                        ->orWhereHas('muvekkil', fn (Builder $muvekkil) => $muvekkil->where('ad', 'like', '%'.$needle.'%'))
                        ->orWhereHas('protokol', fn (Builder $protokol) => $protokol->where('protokol_no', 'like', '%'.$needle.'%'));
                });
            })
            ->when(! empty($filters['onay_durumu']), fn (Builder $query) => $query->where('onay_durumu', $filters['onay_durumu']))
            ->when(! empty($filters['bugun']), fn (Builder $query) => $query->whereDate('tahsilat_tarihi', now()->toDateString()))
            ->when(! empty($filters['tarih_baslangic']) && ! empty($filters['tarih_bitis']), fn (Builder $query) => $query
                ->whereBetween('tahsilat_tarihi', [$filters['tarih_baslangic'], $filters['tarih_bitis']]))
            // YENİ EKLENEN MÜVEKKİL VE PORTFÖY FİLTRELERİ
            ->when(! empty($filters['muvekkil_id']), fn (Builder $query) => $query->where('muvekkil_id', $filters['muvekkil_id']))
            ->when(! empty($filters['portfoy_id']), fn (Builder $query) => $query->whereHas('protokol', fn (Builder $p) => $p->where('portfoy_id', $filters['portfoy_id'])))
            ->orderByDesc('tahsilat_tarihi')
            ->orderByDesc('id');

        $paginator = $query->paginate($perPage)->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()->map(fn (Tahsilat $tahsilat) => $this->toArray($tahsilat))
        );

        return $paginator;
    }

    public function detail(Tahsilat $tahsilat): array
    {
        $tahsilat->loadMissing(['muvekkil', 'protokol.portfoy', 'protokol.taksitler', 'protokol.hacizciler', 'dekontlar']);

        return $this->toArray($tahsilat, withFullProtokol: true);
    }

    public function create(array $data, ?UploadedFile $dekont, User $user): Tahsilat
    {
        return DB::transaction(function () use ($data, $dekont, $user) {
            [$protokol, $taksit, $odemeKalemiTipi] = $this->resolvePaymentTarget($data);
            $tutar = Money::normalize($data['tutar']);

            if ($protokol) {
                $this->assertCollectable($protokol, $odemeKalemiTipi, $tutar, $taksit);
            }

            $tahsilat = Tahsilat::query()->create([
                'protokolsuz' => (bool) ($data['protokolsuz'] ?? false),
                'protokol_id' => $protokol?->id,
                'protokol_taksit_id' => $taksit?->id,
                'odeme_kalemi_tipi' => $odemeKalemiTipi->value,
                'muvekkil_id' => $protokol?->muvekkil_id ?? $data['muvekkil_id'],
                'borclu_adi' => trim((string) ($data['borclu_adi'] ?? $protokol?->borclu_adi ?? '')),
                'borclu_tckn_vkn' => $data['borclu_tckn_vkn'] ?? $protokol?->borclu_tckn_vkn,
                'tahsilat_tarihi' => $data['tahsilat_tarihi'],
                'tutar' => $tutar,
                'tahsilat_yontemi' => $data['tahsilat_yontemi'],
                'pos_cihazi' => $data['pos_cihazi'] ?? null, // YENİ EKLENEN SATIR
                'tahsilat_birimleri' => array_values($data['tahsilat_birimleri']),
                'notlar' => $data['notlar'] ?? null,
                'onay_durumu' => TahsilatOnayDurumu::Beklemede->value,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            if ($dekont) {
                $this->storeDekont($tahsilat, $dekont, $user);
            }

            return $tahsilat->fresh(['muvekkil', 'protokol.portfoy', 'dekontlar']);
        });
    }

    public function update(Tahsilat $tahsilat, array $data, User $user): Tahsilat
    {
        if ($tahsilat->onay_durumu !== TahsilatOnayDurumu::Beklemede->value) {
            throw ValidationException::withMessages([
                'tahsilat' => 'Sadece beklemedeki tahsilatlar güncellenebilir.',
            ]);
        }

        return DB::transaction(function () use ($tahsilat, $data, $user) {
            $tahsilat->loadMissing(['protokol', 'protokolTaksit']);
            $tutar = Money::normalize($data['tutar']);

            if ($tahsilat->protokol) {
                $this->assertCollectable(
                    $tahsilat->protokol,
                    OdemeKalemiTipi::from($tahsilat->odeme_kalemi_tipi),
                    $tutar,
                    $tahsilat->protokolTaksit,
                );
            }

            $tahsilat->update([
                'borclu_adi' => trim((string) $data['borclu_adi']),
                'borclu_tckn_vkn' => $data['borclu_tckn_vkn'] ?? null,
                'tahsilat_tarihi' => $data['tahsilat_tarihi'],
                'tutar' => $tutar,
                'tahsilat_yontemi' => $data['tahsilat_yontemi'],
                'pos_cihazi' => $data['pos_cihazi'] ?? null, // YENİ EKLENEN SATIR
                'tahsilat_birimleri' => array_values($data['tahsilat_birimleri']),
                'notlar' => $data['notlar'] ?? null,
                'updated_by' => $user->id,
            ]);

            return $tahsilat->fresh(['muvekkil', 'protokol.portfoy', 'dekontlar']);
        });
    }

    public function uploadDekont(Tahsilat $tahsilat, UploadedFile $dekont, User $user): TahsilatDekontu
    {
        return DB::transaction(function () use ($tahsilat, $dekont, $user) {
            
            // YENİ: Eğer yeni dekont yükleniyorsa, kafa karışıklığını önlemek için eskileri sil (Üzerine Yaz)
            foreach ($tahsilat->dekontlar as $eskiDekont) {
                Storage::disk($eskiDekont->disk)->delete($eskiDekont->path);
                $eskiDekont->delete();
            }

            return $this->storeDekont($tahsilat, $dekont, $user);
        });
    }

    public function approve(Tahsilat $tahsilat, User $user): Tahsilat
    {
        return DB::transaction(function () use ($tahsilat, $user) {
            $tahsilat = Tahsilat::query()
                ->whereKey($tahsilat->id)
                ->lockForUpdate()
                ->with(['protokol', 'protokolTaksit'])
                ->firstOrFail();

            if ($tahsilat->onay_durumu !== TahsilatOnayDurumu::Beklemede->value) {
                throw ValidationException::withMessages([
                    'tahsilat' => 'Sadece beklemedeki tahsilatlar onaylanabilir.',
                ]);
            }

            if ($tahsilat->protokol) {
                $this->assertCollectable(
                    $tahsilat->protokol,
                    OdemeKalemiTipi::from($tahsilat->odeme_kalemi_tipi),
                    $tahsilat->tutar,
                    $tahsilat->protokolTaksit,
                );

                if ($tahsilat->protokolTaksit) {
                    $tahsilat->protokolTaksit->update([
                        'odenen_tutar' => Money::add($tahsilat->protokolTaksit->odenen_tutar, $tahsilat->tutar),
                    ]);
                }
            }

            $tahsilat->update([
                'onay_durumu' => TahsilatOnayDurumu::Onaylandi->value,
                'onaylayan_user_id' => $user->id,
                'updated_by' => $user->id,
            ]);

            return $tahsilat->fresh(['muvekkil', 'protokol.portfoy', 'dekontlar']);
        });
    }

    public function reject(Tahsilat $tahsilat, string $redNedeni, User $user): Tahsilat
    {
        if ($redNedeni === '') {
            throw ValidationException::withMessages([
                'red_nedeni' => 'Red nedeni zorunludur.',
            ]);
        }

        return DB::transaction(function () use ($tahsilat, $redNedeni, $user) {
            $tahsilat = Tahsilat::query()->whereKey($tahsilat->id)->lockForUpdate()->firstOrFail();

            if ($tahsilat->onay_durumu !== TahsilatOnayDurumu::Beklemede->value) {
                throw ValidationException::withMessages([
                    'tahsilat' => 'Sadece beklemedeki tahsilatlar reddedilebilir.',
                ]);
            }

            $tahsilat->update([
                'onay_durumu' => TahsilatOnayDurumu::Reddedildi->value,
                'red_nedeni' => $redNedeni,
                'reddeden_user_id' => $user->id,
                'updated_by' => $user->id,
            ]);

            return $tahsilat->fresh(['muvekkil', 'protokol.portfoy', 'dekontlar']);
        });
    }

    public function cancel(Tahsilat $tahsilat, ?string $iptalNedeni, User $user): Tahsilat
    {
        return DB::transaction(function () use ($tahsilat, $iptalNedeni, $user) {
            $tahsilat = Tahsilat::query()
                ->whereKey($tahsilat->id)
                ->lockForUpdate()
                ->with(['protokolTaksit'])
                ->firstOrFail();

            if ($tahsilat->onay_durumu !== TahsilatOnayDurumu::Onaylandi->value) {
                throw ValidationException::withMessages([
                    'tahsilat' => 'Sadece onaylı tahsilatlar iptal edilebilir.',
                ]);
            }

            if ($tahsilat->protokolTaksit) {
                $kalanOdenen = Money::max(
                    Money::sub($tahsilat->protokolTaksit->odenen_tutar, $tahsilat->tutar),
                    '0',
                );

                $tahsilat->protokolTaksit->update(['odenen_tutar' => $kalanOdenen]);
            }

            $tahsilat->update([
                'onay_durumu' => TahsilatOnayDurumu::Iptal->value,
                'iptal_nedeni' => $iptalNedeni ?: null,
                'iptal_eden_user_id' => $user->id,
                'updated_by' => $user->id,
            ]);

            return $tahsilat->fresh(['muvekkil', 'protokol.portfoy', 'dekontlar']);
        });
    }

    public function dekontResponse(TahsilatDekontu $dekont)
    {
        return Storage::disk($dekont->disk)->response($dekont->path, $dekont->original_name, [
            'Content-Type' => $dekont->mime_type,
        ]);
    }

    public function toArray(Tahsilat $tahsilat, bool $withFullProtokol = false): array
    {
        $protokolPayload = null;
        if ($tahsilat->protokol) {
            $protokolPayload = $withFullProtokol
                ? $this->protokolService->toArray($tahsilat->protokol)
                : [
                    'id' => (string) $tahsilat->protokol->id,
                    'protokol_no' => $tahsilat->protokol->protokol_no,
                    'muvekkil_id' => (string) $tahsilat->protokol->muvekkil_id,
                    'portfoy_id' => $tahsilat->protokol->portfoy_id ? (string) $tahsilat->protokol->portfoy_id : null,
                ];
        }

        return [
            'id' => (string) $tahsilat->id,
            'protokolsuz' => (bool) $tahsilat->protokolsuz,
            'protokol_id' => $tahsilat->protokol_id ? (string) $tahsilat->protokol_id : null,
            'muvekkil_id' => (string) $tahsilat->muvekkil_id,
            'borclu_adi' => $tahsilat->borclu_adi,
            'borclu_tckn_vkn' => $tahsilat->borclu_tckn_vkn,
            'tahsilat_tarihi' => optional($tahsilat->tahsilat_tarihi)->toDateString(),
            'tutar' => Money::float($tahsilat->tutar),
            'tahsilat_yontemi' => $tahsilat->tahsilat_yontemi,
            'pos_cihazi' => $tahsilat->pos_cihazi, // YENİ EKLENEN SATIR
            'tahsilat_birimleri' => $tahsilat->tahsilat_birimleri ?? [],
            'notlar' => $tahsilat->notlar,
            'onay_durumu' => $tahsilat->onay_durumu,
            'red_nedeni' => $tahsilat->red_nedeni,
            'iptal_nedeni' => $tahsilat->iptal_nedeni,
            'created_by' => (string) $tahsilat->created_by,
            'created_at' => optional($tahsilat->created_at)->toIso8601String(),
            'muvekkil' => $tahsilat->muvekkil ? [
                'id' => (string) $tahsilat->muvekkil->id,
                'ad' => $tahsilat->muvekkil->ad,
            ] : null,
            'portfoy' => $tahsilat->protokol?->portfoy ? [
                'id' => (string) $tahsilat->protokol->portfoy->id,
                'ad' => $tahsilat->protokol->portfoy->ad,
            ] : null,
            'protokol' => $protokolPayload,
            'dekontlar' => $tahsilat->dekontlar->map(fn (TahsilatDekontu $dekont) => [
                'id' => (string) $dekont->id,
                'original_name' => $dekont->original_name,
            ])->all(),
            'tahsilat_turu' => [
                'odeme_kalemi' => $this->odemeKalemiMeta($tahsilat),
            ],
        ];
    }

    private function resolvePaymentTarget(array $data): array
    {
        if ((bool) ($data['protokolsuz'] ?? false)) {
            return [null, null, OdemeKalemiTipi::Serbest];
        }

        $protokol = Protokol::query()
            ->with(['taksitler'])
            ->findOrFail($data['protokol_id']);

        if ((int) $protokol->muvekkil_id !== (int) $data['muvekkil_id']) {
            throw ValidationException::withMessages([
                'muvekkil_id' => 'Seçilen müvekkil protokol ile uyuşmuyor.',
            ]);
        }

        $odemeKalemi = (string) ($data['odeme_kalemi'] ?? '');

        if ($odemeKalemi === 'pesinat') {
            return [$protokol, null, OdemeKalemiTipi::Pesinat];
        }

        if (str_starts_with($odemeKalemi, 'taksit:')) {
            $taksitId = (int) str_replace('taksit:', '', $odemeKalemi);
            $taksit = $protokol->taksitler->firstWhere('id', $taksitId);

            if (! $taksit) {
                throw ValidationException::withMessages([
                    'odeme_kalemi' => 'Seçilen taksit protokole ait değil.',
                ]);
            }

            return [$protokol, $taksit, OdemeKalemiTipi::Taksit];
        }

        throw ValidationException::withMessages([
            'odeme_kalemi' => 'Ödeme kalemi seçimi zorunludur.',
        ]);
    }

    private function assertCollectable(Protokol $protokol, OdemeKalemiTipi $tip, string $tutar, ?ProtokolTaksit $taksit = null): void
    {
        // Kilit (Lock) mekanizmasını eşzamanlı ödeme çakışmalarını önlemek için koruyoruz
        DB::table('protokoller')->where('id', $protokol->id)->lockForUpdate()->get();

        $kalan = match ($tip) {
            OdemeKalemiTipi::Pesinat => $this->lockAndRemainingPesinat($protokol),
            OdemeKalemiTipi::Taksit => $this->lockAndRemainingTaksit($taksit),
            default => $tutar,
        };

        // 2. YOL (YAZILIMSAL ESNEKLİK): Karşı vekalet ücreti gibi fazla ödemeleri alabilmek için
        // limit aşımını engelleyen ve hata fırlatan kuralı devre dışı bıraktık.
        
        /* if (Money::cmp($tutar, $kalan) === 1) {
            throw ValidationException::withMessages([
                'tutar' => 'Girilen tutar kalan tahsil edilebilir tutarı aşıyor.',
            ]);
        }
        */
    }

    private function lockAndRemainingPesinat(Protokol $protokol): string
    {
        $odenen = Tahsilat::query()
            ->where('protokol_id', $protokol->id)
            ->where('odeme_kalemi_tipi', OdemeKalemiTipi::Pesinat->value)
            ->where('onay_durumu', TahsilatOnayDurumu::Onaylandi->value)
            ->sum('tutar');

        return Money::max(Money::sub($protokol->pesinat, $odenen), '0');
    }

    private function lockAndRemainingTaksit(?ProtokolTaksit $taksit): string
    {
        if (! $taksit) {
            throw ValidationException::withMessages([
                'odeme_kalemi' => 'Taksit kaydı bulunamadı.',
            ]);
        }

        $taksit = ProtokolTaksit::query()->whereKey($taksit->id)->lockForUpdate()->firstOrFail();

        return Money::max(Money::sub($taksit->taksit_tutari, $taksit->odenen_tutar), '0');
    }

    private function storeDekont(Tahsilat $tahsilat, UploadedFile $dekont, User $user): TahsilatDekontu
    {
        $path = $dekont->store('tahsilat-dekontlari', 'public');

        return $tahsilat->dekontlar()->create([
            'disk' => 'public',
            'path' => $path,
            'original_name' => $dekont->getClientOriginalName(),
            'mime_type' => $dekont->getMimeType() ?: 'application/octet-stream',
            'size' => $dekont->getSize(),
            'uploaded_by' => $user->id,
        ]);
    }

    private function odemeKalemiMeta(Tahsilat $tahsilat): array
    {
        return match ($tahsilat->odeme_kalemi_tipi) {
            OdemeKalemiTipi::Pesinat->value => ['tip' => 'pesinat'],
            OdemeKalemiTipi::Taksit->value => ['tip' => 'taksit', 'taksit_id' => $tahsilat->protokol_taksit_id ? (string) $tahsilat->protokol_taksit_id : null],
            default => ['tip' => 'serbest'],
        };
    }
}
