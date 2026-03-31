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

                $old = $model->exists ? $model->toArray() : null;
                $model->fill($row);
                $model->save();

                $this->audit('kademe_pay_orani', $model->wasRecentlyCreated ? 'create' : 'update', [
                    'ust_kademe' => $row['ust_kademe'],
                    'alt_kademe' => $row['alt_kademe'],
                ], $old, $model->toArray(), $actor);
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

                $old = $model->exists ? $model->toArray() : null;
                $model->fill($row);
                $model->save();

                $this->audit('kademe_prim_asama_orani', $model->wasRecentlyCreated ? 'create' : 'update', [
                    'kademe' => $row['kademe'],
                    'asama_no' => $row['asama_no'],
                ], $old, $model->toArray(), $actor);
            }
        });
    }

    public function updateHacizciKademe(array $rows, User $actor): void
    {
        DB::transaction(function () use ($rows, $actor) {
            foreach ($rows as $row) {
                $hacizci = Hacizci::query()->findOrFail($row['hacizci_id']);
                $old = $hacizci->only(['kademe']);

                $hacizci->update(['kademe' => $row['kademe']]);

                $this->audit('hacizci_kademe', 'update', [
                    'hacizci_id' => $hacizci->id,
                ], $old, $hacizci->only(['kademe']), $actor);
            }
        });
    }

    public function updateMuvekkilOranlari(array $rows, User $actor): void
    {
        DB::transaction(function () use ($rows, $actor) {
            foreach ($rows as $row) {
                $model = MuvekkilPrimOrani::query()->firstOrNew([
                    'muvekkil_id' => $row['muvekkil_id'],
                ]);

                $old = $model->exists ? $model->toArray() : null;
                $model->fill($row);
                $model->save();

                $this->audit('muvekkil_genel_prim_orani', $model->wasRecentlyCreated ? 'create' : 'update', [
                    'muvekkil_id' => $row['muvekkil_id'],
                ], $old, $model->toArray(), $actor);
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
}
