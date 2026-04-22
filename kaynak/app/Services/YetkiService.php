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
use App\Models\Portfoy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class YetkiService
{
    /**
     * Tüm kullanıcıları ve yetkilerini arayüze gönderir
     */
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
                    'aktif' => (bool) $user->aktif,
                    
                    // Sorumlu Müvekkiller (Veri Filtreleme Güvenlik Duvarı)
                    'sorumlu_muvekkiller' => $yetki->sorumlu_muvekkiller ?? [], 
                    
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

    /**
     * Prim, Kademe, Portföy ve Audit ayarlarını arayüze gönderir
     */
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

            // Portföyleri Müvekkil Adına Göre Alfabetik Sırala
            'portfoyler' => Portfoy::query()->with('muvekkil')->get()->map(fn ($p) => [
                'id' => (string) $p->id,
                'muvekkil_id' => (string) $p->muvekkil_id,
                'muvekkil_ad' => $p->muvekkil?->ad ?? '-',
                'ad' => $p->ad,
                'kod' => $p->kod,
                'aktif' => $p->aktif,
            ])->sortBy(fn($item) => $item['muvekkil_ad'] . ' - ' . $item['ad'])->values()->all(),

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

    // ==========================================
    // KULLANICI, YETKİ & ŞİFRE İŞLEMLERİ
    // ==========================================

    /**
     * Kullanıcıların yetkilerini ve sorumlu müvekkillerini veritabanına kaydeder
     */
    public function updateUserPermissions(User $user, array $payload): TahsilatYetkiliKullanici
    {
        // 1. User tablosundaki aktif durumunu güncelle
        if (isset($payload['aktif'])) {
            $user->update(['aktif' => (bool) $payload['aktif']]);
        }

        return TahsilatYetkiliKullanici::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'tahsilat_olusturabilir' => (bool) ($payload['tahsilat_olusturabilir'] ?? false),
                'protokol_olusturabilir' => (bool) ($payload['protokol_olusturabilir'] ?? false),
                'protokol_duzenleyebilir' => (bool) ($payload['protokol_duzenleyebilir'] ?? false),
                'toplu_protokol_ekleyebilir' => (bool) ($payload['toplu_protokol_ekleyebilir'] ?? false),
                'tahsilat_takip_sorumlusu' => (bool) ($payload['tahsilat_takip_sorumlusu'] ?? false),
                
                // YENİ EKLENEN KISIM: Sorumlu müvekkiller dizisi (Güvenlik filtrelemesi)
                'sorumlu_muvekkiller' => $payload['sorumlu_muvekkiller'] ?? [],
                
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

    public function createKullanici(array $data, User $actor): void
    {
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']), // Şifreyi güvenli kriptola
            'is_admin' => $data['is_admin'] ?? false,
            'aktif' => true,
        ]);

        $this->audit('sistem_kullanicisi', 'create', ['user_id' => $user->id], null, ['email' => $user->email], $actor);
    }

    public function updateSifre(User $user, string $newPassword, User $actor): void
    {
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        $this->audit('kullanici_sifresi', 'update', ['user_id' => $user->id], null, null, $actor);
    }

    // ==========================================
    // KADEME, PRİM & HACİZCİ İŞLEMLERİ
    // ==========================================

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
                
                $old = $hacizci->only(['kademe', 'aktif']);

                $hacizci->fill([
                    'kademe' => $row['kademe'],
                    'aktif' => $row['aktif'] ?? true, 
                ]);

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
        $hacizci = Hacizci::query()->create([
            'ad_soyad' => $data['ad_soyad'],
            'sicil_no' => $data['sicil_no'] ?? null,
            'kademe' => $data['kademe'],
            'aktif' => true, 
        ]);

        $this->audit('hacizci_kademe', 'create', [
            'hacizci_id' => $hacizci->id,
        ], null, $hacizci->only(['kademe', 'aktif']), $actor);
    }

    public function createKademe(array $data, User $actor): void
    {
        DB::transaction(function () use ($data, $actor) {
            $yeniKademeKey = 'kademe_' . $data['kademe_no'];
            
            if (PrimKademe::query()->where('kademe', $yeniKademeKey)->exists()) {
                throw ValidationException::withMessages(['kademe' => 'Bu kademe numarası zaten mevcut!']);
            }

            $kademeModel = PrimKademe::query()->create([
                'kademe' => $yeniKademeKey,
                'kademe_adi' => 'Kademe ' . $data['kademe_no'],
                'varsayilan_prim_orani' => $data['varsayilan_prim_orani'] ?? 0,
                'aktif' => true,
            ]);

            $mevcutKademeler = PrimKademe::query()->where('id', '!=', $kademeModel->id)->get();
            foreach ($mevcutKademeler as $mevcut) {
                $mevcutNo = (int) str_replace('kademe_', '', $mevcut->kademe);
                $yeniNo = (int) $data['kademe_no'];

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

            for ($i = 1; $i <= 3; $i++) {
                PrimKademeAsamasi::query()->create([
                    'kademe' => $yeniKademeKey,
                    'asama_no' => $i,
                    'esik_tutari' => 0,
                    'prim_orani' => 0,
                    'aktif' => true,
                ]);
            }

            $this->audit('prim_kademeler', 'create', ['kademe' => $yeniKademeKey], null, $kademeModel->toArray(), $actor);
        });
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

                if ($model->isDirty() || $isNew) {
                    $model->save();

                    $this->audit('muvekkil_genel_prim_orani', $isNew ? 'create' : 'update', [
                        'muvekkil_id' => $row['muvekkil_id'],
                    ], $old, $model->toArray(), $actor);
                }
            }
        });
    }

    // ==========================================
    // PORTFÖY İŞLEMLERİ
    // ==========================================

    public function createPortfoy(array $data, User $actor): void
    {
        Portfoy::query()->create([
            'muvekkil_id' => $data['muvekkil_id'],
            'ad' => $data['ad'],
            'kod' => $data['kod'] ?? null,
            'normalized_ad' => Str::slug($data['ad'], ' '),
            'aktif' => true,
        ]);
    }

    public function updatePortfoy(int $id, array $data, User $actor): void
    {
        $portfoy = Portfoy::query()->findOrFail($id);
        $portfoy->update([
            'ad' => $data['ad'] ?? $portfoy->ad,
            'kod' => $data['kod'] ?? $portfoy->kod,
            'aktif' => $data['aktif'] ?? $portfoy->aktif,
        ]);
    }

    // ==========================================
    // GÜVENLİK VE AUDIT (LOG) İŞLEMLERİ
    // ==========================================

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
}