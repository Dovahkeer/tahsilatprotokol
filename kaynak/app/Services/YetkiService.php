<?php

namespace App\Services;

use App\Models\Hacizci;
use App\Models\Muvekkil;
use App\Models\MuvekkilPrimOrani;
use App\Models\PrimAuditLogu;
use App\Models\PrimKademe;
use App\Models\PrimKademeAsamasi;
use App\Models\PrimKademePayOrani;
use App\Models\TahsilatYetkiliKullanici;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class YetkiService
{
    public function users(): array
    {
        $tabKeys = collect(config('tahsilat.tab_tanimlari', []))
            ->pluck('key')
            ->all();

        $kullanicilar = User::query()
            ->with('yetkiKaydi')
            ->orderBy('name')
            ->get()
            ->map(function (User $user) use ($tabKeys) {
                $yetki = $user->yetkiKaydi ?? new TahsilatYetkiliKullanici();

                return [
                    'id' => (string) $user->id,
                    'ad' => $user->name,
                    'email' => $user->email,
                    'yonetici' => $user->isAdmin(),
                    'aktif' => (bool) $user->aktif, // <-- BU SATIRI EKLE
                    'tahsilat_olusturabilir' => (bool) $yetki->tahsilat_olusturabilir || $user->isAdmin(),
                    'protokol_olusturabilir' => (bool) $yetki->protokol_olusturabilir || $user->isAdmin(),
                    'protokol_duzenleyebilir' => (bool) $yetki->protokol_duzenleyebilir || $user->isAdmin(),
                    'toplu_protokol_ekleyebilir' => (bool) $yetki->toplu_protokol_ekleyebilir || $user->isAdmin(),
                    'tahsilat_takip_sorumlusu' => (bool) $yetki->tahsilat_takip_sorumlusu || $user->isAdmin(),
                    'tab_permissions' => $user->tabPermissions($tabKeys),
                ];
            })
            ->values()
            ->all();

        return [
            'kullanicilar' => $kullanicilar,
            'tab_tanimlari' => config('tahsilat.tab_tanimlari', []),
        ];
    }

    public function primAyarlar(): array
    {
        $muvekkilRows = Muvekkil::query()
            ->with('primOrani')
            ->orderBy('ad')
            ->get()
            ->map(function (Muvekkil $muvekkil) {
                return [
                    'muvekkil_id' => (string) $muvekkil->id,
                    'muvekkil_ad' => $muvekkil->ad,
                    'prim_orani' => $muvekkil->primOrani?->prim_orani,
                    'aktif' => $muvekkil->primOrani?->aktif ?? true,
                ];
            })
            ->values()
            ->all();

        return [
            'kademeler' => PrimKademe::query()->orderBy('kademe')->get()->toArray(),
            'hacizci_kademeleri' => Hacizci::query()->orderBy('ad_soyad')->get()->map(fn (Hacizci $hacizci) => [
                'hacizci_id' => (string) $hacizci->id,
                'ad_soyad' => $hacizci->ad_soyad,
                'sicil_no' => $hacizci->sicil_no,
                'kademe' => $hacizci->kademe,
                'aktif' => $hacizci->aktif,
            ])->values()->all(),
            'kademe_pay_oranlari' => PrimKademePayOrani::query()->orderBy('ust_kademe')->orderBy('alt_kademe')->get()->toArray(),
            'kademe_prim_asamalari' => PrimKademeAsamasi::query()->orderBy('kademe')->orderBy('asama_no')->get()->toArray(),
            'muvekkil_oranlari' => $muvekkilRows,
            'audit_kayitlari' => PrimAuditLogu::query()
                ->with('degistiren')
                ->latest()
                ->limit(100)
                ->get()
                ->map(fn (PrimAuditLogu $log) => [
                    'id' => (string) $log->id,
                    'alan_tipi' => $log->alan_tipi,
                    'islem_tipi' => $log->islem_tipi,
                    'hedef_anahtar' => $log->hedef_anahtar,
                    'eski_deger' => $log->eski_deger,
                    'yeni_deger' => $log->yeni_deger,
                    'aciklama' => $log->aciklama,
                    'created_at' => optional($log->created_at)->toIso8601String(),
                    'degistiren' => $log->degistiren?->name ?? '-',
                ])
                ->all(),
        ];
    }

    public function updateUserPermissions(User $user, array $payload): TahsilatYetkiliKullanici
    {
        // 1. User tablosundaki aktif durumunu güncelle
        if (isset($payload['aktif'])) {
            $user->update(['aktif' => (bool) $payload['aktif']]);
        }

        return TahsilatYetkiliKullanici::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'tahsilat_olusturabilir' => (bool) $payload['tahsilat_olusturabilir'],
                'protokol_olusturabilir' => (bool) $payload['protokol_olusturabilir'],
                'protokol_duzenleyebilir' => (bool) $payload['protokol_duzenleyebilir'],
                'toplu_protokol_ekleyebilir' => (bool) $payload['toplu_protokol_ekleyebilir'],
                'tahsilat_takip_sorumlusu' => (bool) $payload['tahsilat_takip_sorumlusu'],
                'aktif' => (bool) ($payload['aktif'] ?? true),
                'tab_permissions' => $this->normalizeTabPermissions(
                    $payload['tab_permissions'] ?? [],
                    $user->isAdmin(),
                ),
            ],
        );
    }

    private function normalizeTabPermissions(array $payload, bool $allowAll = false): array
    {
        $tabKeys = collect(config('tahsilat.tab_tanimlari', []))
            ->pluck('key')
            ->all();

        return collect($tabKeys)
            ->mapWithKeys(fn (string $key) => [$key => $allowAll ? true : (bool) ($payload[$key] ?? false)])
            ->all();
    }

    public function updateKademePay(array $rows, User $actor): void
    {
        DB::transaction(function () use ($rows, $actor) {
            foreach ($rows as $row) {
                $toplam = round(((float) $row['ust_kademe_orani']) + ((float) $row['alt_kademe_orani']), 2);
                if (abs($toplam - 100.0) > 0.01) {
                    throw ValidationException::withMessages([
                        'pay_oranlari' => 'Üst ve alt kademe oran toplamı 100 olmalıdır.',
                    ]);
                }

                $model = PrimKademePayOrani::query()->firstOrNew([
                    'ust_kademe' => $row['ust_kademe'],
                    'alt_kademe' => $row['alt_kademe'],
                ]);

                $isNew = !$model->exists;
                $old = $isNew ? null : $model->toArray();
                
                $model->fill($row);

                // SADECE DEĞİŞİKLİK VARSA VEYA YENİ KAYITSA KAYDET VE LOGLA
                if ($model->isDirty() || $isNew) {
                    $model->save();

                    $this->audit('kademe_pay_orani', $isNew ? 'create' : 'update', [
                        'ust_kademe' => $row['ust_kademe'],
                        'alt_kademe' => $row['alt_kademe'],
                    ], $old, $model->toArray(), $actor);
                }
            }
        });
    }

    public function updateKademeAsama(array $rows, User $actor): void
    {
        DB::transaction(function () use ($rows, $actor) {
            foreach ($rows as $row) {
                $model = PrimKademeAsamasi::query()->firstOrNew([
                    'kademe' => $row['kademe'],
                    'asama_no' => $row['asama_no'],
                ]);

                $isNew = !$model->exists;
                $old = $isNew ? null : $model->toArray();
                
                $model->fill($row);

                // SADECE DEĞİŞİKLİK VARSA VEYA YENİ KAYITSA KAYDET VE LOGLA
                if ($model->isDirty() || $isNew) {
                    $model->save();

                    $this->audit('kademe_prim_asama_orani', $isNew ? 'create' : 'update', [
                        'kademe' => $row['kademe'],
                        'asama_no' => $row['asama_no'],
                    ], $old, $model->toArray(), $actor);
                }
            }
        });
    }

    public function updateHacizciKademe(array $rows, User $actor): void
    {
        DB::transaction(function () use ($rows, $actor) {
            foreach ($rows as $row) {
                $hacizci = Hacizci::query()->findOrFail($row['hacizci_id']);
                
                // Aktiflik durumunu da loga dahil ediyoruz
                $old = $hacizci->only(['kademe', 'aktif']);

                $hacizci->fill([
                    'kademe' => $row['kademe'],
                    'aktif' => $row['aktif'] ?? true, // Arayüzden gelen toggle
                ]);

                // SADECE DEĞİŞİKLİK VARSA KAYDET VE LOGLA
                if ($hacizci->isDirty()) {
                    $hacizci->save();

                    $this->audit('hacizci_kademe', 'update', [
                        'hacizci_id' => $hacizci->id,
                    ], $old, $hacizci->only(['kademe', 'aktif']), $actor);
                }
            }
        });
    }

    public function createHacizci(array $data, User $actor): void
    {
        // 1. Yeni hacizciyi oluştur (varsayılan olarak aktif: true yapıyoruz)
        $hacizci = Hacizci::query()->create([
            'ad_soyad' => $data['ad_soyad'],
            'sicil_no' => $data['sicil_no'] ?? null,
            'kademe' => $data['kademe'],
            'aktif' => true, 
        ]);

        // 2. Sistemin tarihçesi (Audit) için bunu logla
        $this->audit('hacizci_kademe', 'create', [
            'hacizci_id' => $hacizci->id,
        ], null, $hacizci->only(['kademe', 'aktif']), $actor);
    }

    public function updateMuvekkilOranlari(array $rows, User $actor): void
    {
        DB::transaction(function () use ($rows, $actor) {
            foreach ($rows as $row) {
                $model = MuvekkilPrimOrani::query()->firstOrNew([
                    'muvekkil_id' => $row['muvekkil_id'],
                ]);

                $isNew = !$model->exists;
                $old = $isNew ? null : $model->toArray();
                
                $model->fill($row);

                // SADECE DEĞİŞİKLİK VARSA VEYA YENİ KAYITSA KAYDET VE LOGLA
                if ($model->isDirty() || $isNew) {
                    $model->save();

                    $this->audit('muvekkil_genel_prim_orani', $isNew ? 'create' : 'update', [
                        'muvekkil_id' => $row['muvekkil_id'],
                    ], $old, $model->toArray(), $actor);
                }
            }
        });
    }

    private function audit(string $alan, string $islem, array $anahtar, mixed $eski, mixed $yeni, User $actor): void
    {
        PrimAuditLogu::query()->create([
            'alan_tipi' => $alan,
            'islem_tipi' => $islem,
            'hedef_anahtar' => $anahtar,
            'eski_deger' => $eski,
            'yeni_deger' => $yeni,
            'changed_by' => $actor->id,
        ]);
    }

    public function createKademe(array $data, User $actor): void
    {
        DB::transaction(function () use ($data, $actor) {
            // 1. Yeni Kademeyi Ekle (Örn: kademe_4)
            $yeniKademeKey = 'kademe_' . $data['kademe_no'];
            
            // Eğer bu kademe zaten varsa işlemi durdur
            if (PrimKademe::query()->where('kademe', $yeniKademeKey)->exists()) {
                throw ValidationException::withMessages(['kademe' => 'Bu kademe numarası zaten mevcut!']);
            }

            $kademeModel = PrimKademe::query()->create([
                'kademe' => $yeniKademeKey,
                'kademe_adi' => 'Kademe ' . $data['kademe_no'],
                'varsayilan_prim_orani' => $data['varsayilan_prim_orani'] ?? 0,
                'aktif' => true,
            ]);

            // 2. MATRİSİ OTOMATİK DOLDUR (%50 - %50)
            $mevcutKademeler = PrimKademe::query()->where('id', '!=', $kademeModel->id)->get();
            foreach ($mevcutKademeler as $mevcut) {
                $mevcutNo = (int) str_replace('kademe_', '', $mevcut->kademe);
                $yeniNo = (int) $data['kademe_no'];

                // Küçük numara üst kademedir kuralı (Örn: Kademe 1 üst, Kademe 4 alttır)
                $ust = $mevcutNo < $yeniNo ? $mevcut->kademe : $yeniKademeKey;
                $alt = $mevcutNo < $yeniNo ? $yeniKademeKey : $mevcut->kademe;

                PrimKademePayOrani::query()->firstOrCreate([
                    'ust_kademe' => $ust,
                    'alt_kademe' => $alt,
                ], [
                    'ust_kademe_orani' => 50,
                    'alt_kademe_orani' => 50,
                    'aktif' => true,
                ]);
            }

            // 3. ZORUNLU 3 AŞAMAYI OTOMATİK OLUŞTUR (0 TL ile)
            for ($i = 1; $i <= 3; $i++) {
                PrimKademeAsamasi::query()->create([
                    'kademe' => $yeniKademeKey,
                    'asama_no' => $i,
                    'esik_tutari' => 0,
                    'prim_orani' => 0,
                    'aktif' => true,
                ]);
            }

            // Audit
            $this->audit('prim_kademeler', 'create', ['kademe' => $yeniKademeKey], null, $kademeModel->toArray(), $actor);
        });
    }
}
