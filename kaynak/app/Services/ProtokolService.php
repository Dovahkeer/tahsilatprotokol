<?php

namespace App\Services;

use App\Models\Hacizci;
use App\Models\Protokol;
use App\Models\ProtokolTaksit;
use App\Models\Tahsilat;
use App\Models\User;
use App\Support\Money;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProtokolService
{
    public function __construct(
        private readonly ProtokolNoService $protokolNoService,
    ) {
    }

    public function paginate(array $filters, User $user): array
    {
        $perPage = max(1, min((int) ($filters['per_page'] ?? 20), 100));
        $ay = ! empty($filters['ay']) ? Carbon::parse($filters['ay'].'-01') : now();

        // 1. ADIM: Temel sorguyu (filtreleri) oluştur ama henüz veriyi çekme
        $baseQuery = Protokol::query()
            ->with(['muvekkil', 'portfoy', 'taksitler', 'hacizciler'])
            ->when(! empty($filters['protokol_no']), fn ($q) => $q->where('protokol_no', 'like', '%'.$filters['protokol_no'].'%'))
            ->when(! empty($filters['muvekkil_ids']), fn ($q) => $q->whereIn('muvekkil_id', $filters['muvekkil_ids']))
            ->when(! empty($filters['portfoy_id']), fn ($q) => $q->where('portfoy_id', $filters['portfoy_id']))
            ->when(! empty($filters['baslangic_tarihi']), fn ($q) => $q->whereDate('protokol_tarihi', '>=', $filters['baslangic_tarihi']))
            ->when(! empty($filters['bitis_tarihi']), fn ($q) => $q->whereDate('protokol_tarihi', '<=', $filters['bitis_tarihi']))
            ->when(($filters['aktif_durumu'] ?? 'aktif') === 'aktif', fn ($q) => $q->where('aktif', true))
            ->when(($filters['aktif_durumu'] ?? 'aktif') === 'pasif', fn ($q) => $q->where('aktif', false));

        // 2. ADIM: Sayfalamadan ÖNCE filtrelere uyan TÜM kayıtların genel özetini hesapla
        $globalSummary = $this->calculateGlobalSummary(clone $baseQuery, $ay);

        // 3. ADIM: Şimdi klonladığımız sorguyu sayfalamak için kullan
        $query = clone $baseQuery;

        match ($filters['siralama'] ?? 'protokol_no_desc') {
            'aylik_tutar_desc' => $query->orderByDesc('toplam_protokol_tutari'),
            'protokol_tarihi_desc' => $query->orderByDesc('protokol_tarihi')->orderByDesc('id'),
            'protokol_no_desc' => $query->orderByDesc('id'),
            default => $query->orderByDesc('id'),
        };

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($perPage)->withQueryString();

        $collection = $paginator->getCollection()
            ->map(fn (Protokol $protokol) => $this->toArray($protokol, $user, $ay));

        $paginator->setCollection($collection);

        return [
            'paginator' => $paginator,
            'filtre_taksit_ozeti' => $globalSummary, // Artık sadece o sayfanın değil, GENEL TOPLAMI gönderiyoruz!
        ];
    }

    public function detail(Protokol $protokol, User $user): array
    {
        $protokol->loadMissing(['muvekkil', 'portfoy', 'taksitler', 'hacizciler']);

        return $this->toArray($protokol, $user);
    }

    public function forTahsilatSearch(?string $borcluAdi, ?string $borcluTcknVkn, int $limit = 25): Collection
    {
        $query = Protokol::query()
            ->with(['muvekkil', 'taksitler'])
            ->where('aktif', true)
            ->when($borcluAdi, fn ($q) => $q->where('borclu_adi', 'like', '%'.$borcluAdi.'%'))
            ->when($borcluTcknVkn, fn ($q) => $q->where('borclu_tckn_vkn', 'like', '%'.$borcluTcknVkn.'%'))
            ->orderByDesc('protokol_tarihi')
            ->limit($limit);

        return $query->get()->map(fn (Protokol $protokol) => $this->toArray($protokol));
    }

    public function create(array $data, User $user): Protokol
    {
        return DB::transaction(function () use ($data, $user) {
            $protokolTarihi = Carbon::parse($data['protokol_tarihi']);
            $pesinat = Money::normalize($data['pesinat'] ?? '0');
            $toplam = Money::normalize($data['toplam_protokol_tutari']);
            $taksitler = collect($data['taksitler'] ?? []);
            $taksitToplami = $taksitler->reduce(
                fn (string $toplamTutar, array $taksit) => Money::add($toplamTutar, $taksit['taksit_tutari']),
                '0.00',
            );

            if (Money::cmp(Money::add($pesinat, $taksitToplami), $toplam) !== 0) {
                throw ValidationException::withMessages([
                    'toplam_protokol_tutari' => 'Peşinat ve taksit toplamı protokol toplamına eşit olmalıdır.',
                ]);
            }

            $protokol = Protokol::query()->create([
                'protokol_no' => $this->protokolNoService->generate($protokolTarihi),
                'muvekkil_id' => $data['muvekkil_id'],
                'portfoy_id' => $data['portfoy_id'] ?? null,
                'protokol_tarihi' => $protokolTarihi->toDateString(),
                'borclu_adi' => trim($data['borclu_adi']),
                'borclu_tckn_vkn' => $data['borclu_tckn_vkn'] ?? null,
                'muhatap_adi' => $data['muhatap_adi'] ?? null,
                'muhatap_telefon' => $data['muhatap_telefon'] ?? null,
                'pesinat' => $pesinat,
                'toplam_protokol_tutari' => $toplam,
                'ana_para' => isset($data['ana_para']) ? Money::normalize($data['ana_para']) : null, // EKLE
                'kapak_hesabi' => isset($data['kapak_hesabi']) ? Money::normalize($data['kapak_hesabi']) : null, // EKLE
                'aktif' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            foreach ($taksitler->values() as $index => $taksit) {
                // Önce klasik taksit kaydını ana tabloya atıyoruz
                $yeniTaksit = $protokol->taksitler()->create([
                    'taksit_no' => $index + 1,
                    'taksit_tarihi' => $taksit['taksit_tarihi'],
                    'taksit_tutari' => Money::normalize($taksit['taksit_tutari']),
                    'odenen_tutar' => '0.00',
                ]);

                // Eğer ödeme tipi Çek veya Senet ise, uzantı tablosuna bilgileri yapıştırıyoruz!
                $odemeTipi = $taksit['odeme_tipi'] ?? 'taksit';
                if ($odemeTipi === 'cek' || $odemeTipi === 'senet') {
                    $yeniTaksit->evrakDetayi()->create([
                        'evrak_tipi' => $odemeTipi,
                        'banka_adi' => $taksit['banka_adi'] ?? null,
                        'seri_no' => $taksit['seri_no'] ?? null,
                        'kesideci' => $taksit['kesideci'] ?? null,
                    ]);
                }
            }

            $this->syncHacizciler($protokol, $data['hacizciler']);

            return $protokol->fresh(['muvekkil', 'portfoy', 'taksitler', 'hacizciler']);
        });
    }

    public function update(Protokol $protokol, array $data, User $user): Protokol
    {
        return DB::transaction(function () use ($protokol, $data, $user) {
            $existingTaksitToplami = $protokol->taksitler()
                ->get()
                ->reduce(fn (string $toplam, ProtokolTaksit $taksit) => Money::add($toplam, $taksit->taksit_tutari), '0.00');

            $pesinat = Money::normalize($data['pesinat'] ?? '0');
            $toplam = Money::normalize($data['toplam_protokol_tutari']);

            if (Money::cmp(Money::add($pesinat, $existingTaksitToplami), $toplam) !== 0) {
                throw ValidationException::withMessages([
                    'toplam_protokol_tutari' => 'Mevcut taksit planı ile peşinat toplamı protokol tutarına eşit olmalıdır.',
                ]);
            }

            $protokol->update([
                'muvekkil_id' => $data['muvekkil_id'],
                'portfoy_id' => $data['portfoy_id'] ?? null,
                'protokol_tarihi' => $data['protokol_tarihi'],
                'borclu_adi' => trim($data['borclu_adi']),
                'borclu_tckn_vkn' => $data['borclu_tckn_vkn'] ?? null,
                'muhatap_adi' => $data['muhatap_adi'] ?? null,
                'muhatap_telefon' => $data['muhatap_telefon'] ?? null,
                'pesinat' => $pesinat,
                'toplam_protokol_tutari' => $toplam,
                'ana_para' => isset($data['ana_para']) ? Money::normalize($data['ana_para']) : null, // EKLE
                'kapak_hesabi' => isset($data['kapak_hesabi']) ? Money::normalize($data['kapak_hesabi']) : null, // EKLE
                'aktif' => array_key_exists('aktif', $data) ? (bool) $data['aktif'] : $protokol->aktif,
                'updated_by' => $user->id,
            ]);

            $this->syncHacizciler($protokol, $data['hacizciler']);

            return $protokol->fresh(['muvekkil', 'portfoy', 'taksitler', 'hacizciler']);
        });
    }

    public function uploadPdf(Protokol $protokol, UploadedFile $file): Protokol
    {
        $path = '';
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';

        // Eğer dosya PDF ise Ghostscript sıkıştırmasını çalıştır
        if ($mimeType === 'application/pdf') {
            $originalPath = $file->getRealPath();
            $compressedPath = $originalPath . '_compressed.pdf';

            // İşletim sistemini algıla
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            $gsBinary = $isWindows ? 'gswin64c' : 'gs';

            $gsCommand = sprintf(
                "%s -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/screen -dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s %s",
                $gsBinary,
                escapeshellarg($compressedPath),
                escapeshellarg($originalPath)
            );
            
            exec($gsCommand, $output, $returnCode);

            if ($returnCode === 0 && file_exists($compressedPath)) {
                $path = \Illuminate\Support\Facades\Storage::disk('public')->putFileAs(
                    'protokoller', 
                    new \Illuminate\Http\File($compressedPath), 
                    $file->hashName()
                );
                unlink($compressedPath); // İşimiz bitince geçici dosyayı temizle
            } else {
                $path = $file->store('protokoller', 'public');
            }
        } else {
            $path = $file->store('protokoller', 'public');
        }

        $protokol->update(['protokol_pdf_dosya_yolu' => $path]);

        return $protokol->fresh();
    }

    public function pdfResponse(Protokol $protokol)
    {
        abort_unless($protokol->protokol_pdf_dosya_yolu, 404);

        return Storage::disk('public')->response(
            $protokol->protokol_pdf_dosya_yolu,
            basename($protokol->protokol_pdf_dosya_yolu),
            ['Content-Type' => 'application/pdf'],
        );
    }

    public function calculatePesinatKalan(Protokol $protokol): string
    {
        $odenen = Tahsilat::query()
            ->where('protokol_id', $protokol->id)
            ->where('odeme_kalemi_tipi', 'pesinat')
            ->where('onay_durumu', 'onaylandi')
            ->sum('tutar');

        return Money::max(Money::sub($protokol->pesinat, $odenen), '0');
    }

    public function taksitlerForResponse(Protokol $protokol): array
    {
        return $protokol->taksitler->map(function (ProtokolTaksit $taksit) {
            $kalan = Money::max(Money::sub($taksit->taksit_tutari, $taksit->odenen_tutar), '0');

            return [
                'id' => (string) $taksit->id,
                'taksit_id' => (string) $taksit->id,
                'taksit_no' => $taksit->taksit_no,
                'taksit_tarihi' => optional($taksit->taksit_tarihi)->toDateString(),
                'taksit_tutari' => Money::float($taksit->taksit_tutari),
                'odenen_tutar' => Money::float($taksit->odenen_tutar),
                'kalan_tutar' => Money::float($kalan),
                'odendi' => Money::cmp($kalan, '0') === 0,
            ];
        })->all();
    }

    public function toArray(Protokol $protokol, ?User $user = null, ?CarbonInterface $ozetAy = null): array
    {
        $ozetAy ??= now();
        $monthStart = $ozetAy->copy()->startOfMonth();
        $monthEnd = $ozetAy->copy()->endOfMonth();
        $today = now()->startOfDay();

        $pesinatKalan = $this->calculatePesinatKalan($protokol);
        $taksitler = collect($this->taksitlerForResponse($protokol));
        $kalanTaksitToplami = $taksitler->reduce(fn (string $toplam, array $taksit) => Money::add($toplam, $taksit['kalan_tutar']), '0.00');

        $buAyTaksitler = $taksitler->filter(fn (array $taksit) => Carbon::parse($taksit['taksit_tarihi'])->between($monthStart, $monthEnd));
        $buAyBeklenen = $buAyTaksitler->reduce(
            fn (string $toplam, array $taksit) => Money::add($toplam, $taksit['kalan_tutar']),
            '0.00',
        );
        $vadesiGecmisler = $buAyTaksitler->filter(
            fn (array $taksit) => Carbon::parse($taksit['taksit_tarihi'])->lt($today) && Money::cmp($taksit['kalan_tutar'], '0') === 1,
        );

        return [
            'id' => (string) $protokol->id,
            'protokol_no' => $protokol->protokol_no,
            'muvekkil_id' => (string) $protokol->muvekkil_id,
            'portfoy_id' => $protokol->portfoy_id ? (string) $protokol->portfoy_id : null,
            'protokol_tarihi' => optional($protokol->protokol_tarihi)->toDateString(),
            'borclu_adi' => $protokol->borclu_adi,
            'borclu_tckn_vkn' => $protokol->borclu_tckn_vkn,
            'muhatap_adi' => $protokol->muhatap_adi,
            'muhatap_telefon' => $protokol->muhatap_telefon,
            'pesinat' => Money::float($protokol->pesinat),
            'pesinat_kalan' => Money::float($pesinatKalan),
            'toplam_protokol_tutari' => Money::float($protokol->toplam_protokol_tutari),
            'ana_para' => $protokol->ana_para ? Money::float($protokol->ana_para) : null, // EKLE
            'kapak_hesabi' => $protokol->kapak_hesabi ? Money::float($protokol->kapak_hesabi) : null, // EKLE
            'aktif' => (bool) $protokol->aktif,
            'protokol_pdf_dosya_yolu' => $protokol->protokol_pdf_dosya_yolu,
            'muvekkil' => $protokol->muvekkil ? [
                'id' => (string) $protokol->muvekkil->id,
                'ad' => $protokol->muvekkil->ad,
            ] : null,
            'portfoy' => $protokol->portfoy ? [
                'id' => (string) $protokol->portfoy->id,
                'ad' => $protokol->portfoy->ad,
            ] : null,
            'taksitler' => $taksitler->all(),
            'hacizciler' => $protokol->hacizciler->map(fn (Hacizci $hacizci) => [
                'id' => (string) $hacizci->id,
                'ad_soyad' => $hacizci->ad_soyad,
                'sicil_no' => $hacizci->sicil_no,
                'kademe' => $hacizci->kademe,
                'pivot' => [
                    'haciz_turu' => $hacizci->pivot->haciz_turu,
                    'pay_orani' => $hacizci->pivot->pay_orani !== null ? (float) $hacizci->pivot->pay_orani : null,
                ],
            ])->all(),
            'kalan_taksit_toplami' => Money::float($kalanTaksitToplami),
            'bu_ay_kalan_taksit_toplami' => Money::float($buAyBeklenen),
            'bu_ay_vadesi_gecmis_taksit_sayisi' => $vadesiGecmisler->count(),
            'bu_ay_vadesi_gecmis_taksit_toplami' => Money::float(
                $vadesiGecmisler->reduce(fn (string $toplam, array $taksit) => Money::add($toplam, $taksit['kalan_tutar']), '0.00'),
            ),
            'duzenlenebilir' => $user ? $this->canEdit($protokol, $user) : true,
        ];
    }

    public function canEdit(Protokol $protokol, User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ((int) $protokol->created_by === (int) $user->id) {
            return true;
        }

        return (bool) optional($user->yetkiKaydi)->protokol_duzenleyebilir;
    }

    public function vadeTakipListesi(): array
    {
        $bugun = now()->startOfDay();
        $limitTarih = now()->addDays(7)->endOfDay(); // Önümüzdeki 7 gün

        // Sadece aktif protokollerdeki, henüz tamamen ödenmemiş ve vadesi 7 gün sonrasına kadar olanları getir
        $taksitler = ProtokolTaksit::query()
            ->with(['protokol.muvekkil', 'evrakDetayi'])
            ->whereHas('protokol', fn ($q) => $q->where('aktif', true))
            ->whereRaw('odenen_tutar < taksit_tutari')
            ->whereDate('taksit_tarihi', '<=', $limitTarih)
            ->orderBy('taksit_tarihi', 'asc')
            ->get();

        $gecikmis = [];
        $bugunOdenecekler = [];
        $yaklasanlar = [];

        foreach ($taksitler as $taksit) {
            $vade = Carbon::parse($taksit->taksit_tarihi)->startOfDay();
            $kalanTutar = Money::max(Money::sub($taksit->taksit_tutari, $taksit->odenen_tutar), '0');

            $data = [
                'id' => $taksit->id,
                'protokol_id' => $taksit->protokol_id,
                'protokol_no' => $taksit->protokol->protokol_no,
                'muvekkil_adi' => $taksit->protokol->muvekkil->ad ?? '-',
                'borclu_adi' => $taksit->protokol->borclu_adi,
                'vade_tarihi' => $taksit->taksit_tarihi->format('d.m.Y'), // Ekranda güzel görünsün
                'kalan_tutar' => Money::float($kalanTutar),
                'odeme_tipi' => $taksit->evrakDetayi->evrak_tipi ?? 'taksit',
                'evrak_detayi' => $taksit->evrakDetayi ? [
                    'banka_adi' => $taksit->evrakDetayi->banka_adi,
                    'seri_no' => $taksit->evrakDetayi->seri_no,
                    'kesideci' => $taksit->evrakDetayi->kesideci,
                ] : null,
            ];

            if ($vade->lt($bugun)) {
                $gecikmis[] = $data;
            } elseif ($vade->equalTo($bugun)) {
                $bugunOdenecekler[] = $data;
            } else {
                $yaklasanlar[] = $data;
            }
        }

        return [
            'gecikmis' => $gecikmis,
            'bugun' => $bugunOdenecekler,
            'yaklasanlar' => $yaklasanlar,
        ];
    }

    public function splitTaksitExpectation(Collection $protokoller, CarbonInterface $ay): array
    {
        $ay = $ay->copy();

        return $this->buildFilterSummary($protokoller, $ay);
    }

    private function buildFilterSummary(Collection $protokoller, CarbonInterface $ay): array
    {
        $toplamBeklenen = '0.00';
        $toplamVadesiGecmis = '0.00';
        $vadesiGecmisSayisi = 0;
        $bekleyenProtokolSayisi = 0;

        foreach ($protokoller as $protokol) {
            $beklenen = $protokol['bu_ay_kalan_taksit_toplami'] ?? 0;
            $vadesiGecmis = $protokol['bu_ay_vadesi_gecmis_taksit_toplami'] ?? 0;
            $vadesiGecmisSayisi += (int) ($protokol['bu_ay_vadesi_gecmis_taksit_sayisi'] ?? 0);

            if ((float) $beklenen > 0) {
                $bekleyenProtokolSayisi++;
            }

            $toplamBeklenen = Money::add($toplamBeklenen, $beklenen);
            $toplamVadesiGecmis = Money::add($toplamVadesiGecmis, $vadesiGecmis);
        }

        return [
            'ozet_ay' => $ay->format('Y-m'),
            'beklenen_taksit_toplami' => Money::float($toplamBeklenen),
            'beklenen_protokol_sayisi' => $bekleyenProtokolSayisi,
            'vadesi_gecmis_taksit_sayisi' => $vadesiGecmisSayisi,
            'vadesi_gecmis_taksit_toplami' => Money::float($toplamVadesiGecmis),
        ];
    }

    private function syncHacizciler(Protokol $protokol, array $hacizciler): void
    {
        $syncPayload = collect($hacizciler)
            ->mapWithKeys(fn (array $hacizci) => [
                $hacizci['hacizci_id'] => [
                    'haciz_turu' => $hacizci['haciz_turu'],
                    'pay_orani' => $hacizci['pay_orani'] !== null ? Money::normalize($hacizci['pay_orani']) : null,
                ],
            ])->all();

        $protokol->hacizciler()->sync($syncPayload);
    }

    private function calculateGlobalSummary(\Illuminate\Database\Eloquent\Builder $query, CarbonInterface $ay): array
    {
        $monthStart = $ay->copy()->startOfMonth()->toDateString();
        $monthEnd = $ay->copy()->endOfMonth()->toDateString();
        $today = now()->startOfDay();

        // Ana sorgudan sadece Protokol ID'lerini alıyoruz (Belleği yormamak için)
        $query->select('protokoller.id');

        // Sadece filtrelenen protokollere ait ve o ay içindeki "tamamlanmamış" taksitleri veritabanından çek
        $taksitler = ProtokolTaksit::query()
            ->whereIn('protokol_id', $query)
            ->whereBetween('taksit_tarihi', [$monthStart, $monthEnd])
            ->whereRaw('odenen_tutar < taksit_tutari')
            ->get(['protokol_id', 'taksit_tarihi', 'taksit_tutari', 'odenen_tutar']);

        $toplamBeklenen = '0.00';
        $toplamVadesiGecmis = '0.00';
        $vadesiGecmisSayisi = 0;
        $bekleyenProtokolIds = [];

        foreach ($taksitler as $taksit) {
            $kalan = Money::max(Money::sub($taksit->taksit_tutari, $taksit->odenen_tutar), '0');

            if (Money::cmp($kalan, '0') === 1) {
                $toplamBeklenen = Money::add($toplamBeklenen, $kalan);
                $bekleyenProtokolIds[$taksit->protokol_id] = true;

                // Vadesi bugün veya bugünden önce geçmişse
                if (Carbon::parse($taksit->taksit_tarihi)->lt($today)) {
                    $toplamVadesiGecmis = Money::add($toplamVadesiGecmis, $kalan);
                    $vadesiGecmisSayisi++;
                }
            }
        }

        return [
            'ozet_ay' => $ay->format('Y-m'),
            'beklenen_taksit_toplami' => Money::float($toplamBeklenen),
            'beklenen_protokol_sayisi' => count($bekleyenProtokolIds),
            'vadesi_gecmis_taksit_sayisi' => $vadesiGecmisSayisi,
            'vadesi_gecmis_taksit_toplami' => Money::float($toplamVadesiGecmis),
        ];
    }

}
