<?php

namespace Tests\Feature;

use App\Enums\TahsilatOnayDurumu;
use App\Models\Hacizci;
use App\Models\Muvekkil;
use App\Models\MuvekkilPrimOrani;
use App\Models\Protokol;
use App\Models\Tahsilat;
use App\Models\TahsilatYetkiliKullanici;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use LogicException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TahsilatPanelTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::query()->where('is_admin', true)->firstOrFail();
    }

    public function test_protocol_number_is_generated_in_expected_format(): void
    {
        $muvekkil = Muvekkil::query()->firstOrFail();
        $hacizciler = Hacizci::query()->take(3)->get();

        $response = $this->actingAs($this->admin)->postJson('/tahsilat/protokol/store', [
            'muvekkil_id' => $muvekkil->id,
            'portfoy_id' => null,
            'protokol_tarihi' => '2026-03-27',
            'borclu_adi' => 'Ali Veli',
            'borclu_tckn_vkn' => '12345678901',
            'muhatap_adi' => 'Mehmet Yetkili',
            'muhatap_telefon' => '05550000000',
            'pesinat' => '1000.00',
            'toplam_protokol_tutari' => '1500.00',
            'hacizciler' => [
                ['hacizci_id' => $hacizciler[0]->id, 'haciz_turu' => 'istihkakli', 'pay_orani' => 40],
                ['hacizci_id' => $hacizciler[1]->id, 'haciz_turu' => 'nami_mustear', 'pay_orani' => 30],
                ['hacizci_id' => $hacizciler[2]->id, 'haciz_turu' => '97', 'pay_orani' => 30],
            ],
            'taksitler' => [
                ['taksit_tarihi' => '2026-04-10', 'taksit_tutari' => '500.00'],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertMatchesRegularExpression('/^PRT-2026-\d{6}$/', $response->json('protokol.protokol_no'));
    }

    public function test_three_hacizci_protocol_requires_share_total_of_100(): void
    {
        $muvekkil = Muvekkil::query()->firstOrFail();
        $hacizciler = Hacizci::query()->take(3)->get();

        $response = $this->actingAs($this->admin)->postJson('/tahsilat/protokol/store', [
            'muvekkil_id' => $muvekkil->id,
            'portfoy_id' => null,
            'protokol_tarihi' => '2026-03-27',
            'borclu_adi' => 'Ali Veli',
            'borclu_tckn_vkn' => '12345678901',
            'muhatap_adi' => 'Mehmet Yetkili',
            'muhatap_telefon' => '05550000000',
            'pesinat' => '1000.00',
            'toplam_protokol_tutari' => '1000.00',
            'hacizciler' => [
                ['hacizci_id' => $hacizciler[0]->id, 'haciz_turu' => 'istihkakli', 'pay_orani' => 40],
                ['hacizci_id' => $hacizciler[1]->id, 'haciz_turu' => 'nami_mustear', 'pay_orani' => 30],
                ['hacizci_id' => $hacizciler[2]->id, 'haciz_turu' => '97', 'pay_orani' => 20],
            ],
            'taksitler' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['hacizciler']);
    }

    public function test_collection_requires_receipt_and_starts_as_pending(): void
    {
        $protokol = $this->createProtocol();

        $withoutDekont = $this->actingAs($this->admin)->post('/tahsilat/store', [
            'protokolsuz' => '0',
            'protokol_id' => $protokol['id'],
            'odeme_kalemi' => 'pesinat',
            'muvekkil_id' => $protokol['muvekkil_id'],
            'borclu_adi' => 'Ali Veli',
            'borclu_tckn_vkn' => '12345678901',
            'tahsilat_tarihi' => '2026-03-27',
            'tutar' => '1000.00',
            'tahsilat_yontemi' => 'elden_alindi',
            'tahsilat_birimleri' => ['sulhen'],
        ], ['Accept' => 'application/json']);

        $withoutDekont->assertStatus(422)
            ->assertJsonValidationErrors(['dekont']);

        $withDekont = $this->actingAs($this->admin)->post('/tahsilat/store', [
            'protokolsuz' => '0',
            'protokol_id' => $protokol['id'],
            'odeme_kalemi' => 'pesinat',
            'muvekkil_id' => $protokol['muvekkil_id'],
            'borclu_adi' => 'Ali Veli',
            'borclu_tckn_vkn' => '12345678901',
            'tahsilat_tarihi' => '2026-03-27',
            'tutar' => '1000.00',
            'tahsilat_yontemi' => 'elden_alindi',
            'tahsilat_birimleri' => ['sulhen'],
            'dekont' => UploadedFile::fake()->create('dekont.pdf', 20, 'application/pdf'),
        ], ['Accept' => 'application/json']);

        $withDekont->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('tahsilat.onay_durumu', TahsilatOnayDurumu::Beklemede->value);
    }

    public function test_approval_prevents_over_collection_and_cancel_reverses_effect(): void
    {
        $protokol = $this->createProtocolWithSingleInstallment();

        $first = $this->createPendingTahsilat($protokol['id'], $protokol['muvekkil_id'], 'taksit:'.$protokol['taksit_id'], '60.00');
        $second = $this->createPendingTahsilat($protokol['id'], $protokol['muvekkil_id'], 'taksit:'.$protokol['taksit_id'], '50.00');

        $this->actingAs($this->admin)->postJson('/tahsilat/'.$first.'/onayla')->assertOk();

        $this->actingAs($this->admin)->postJson('/tahsilat/'.$second.'/onayla')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['tutar']);

        $this->actingAs($this->admin)->postJson('/tahsilat/'.$first.'/iptal', ['iptal_nedeni' => 'Test iptal'])
            ->assertOk();

        $this->actingAs($this->admin)->postJson('/tahsilat/'.$second.'/onayla')->assertOk();

        $this->assertDatabaseHas('tahsilatlar', [
            'id' => $second,
            'onay_durumu' => TahsilatOnayDurumu::Onaylandi->value,
        ]);
    }

    public function test_rejection_requires_reason(): void
    {
        $protokol = $this->createProtocol();
        $tahsilatId = $this->createPendingTahsilat($protokol['id'], $protokol['muvekkil_id'], 'pesinat', '200.00');

        $this->actingAs($this->admin)->postJson('/tahsilat/'.$tahsilatId.'/reddet', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['red_nedeni']);
    }

    public function test_dashboard_history_import_does_not_leak_into_main_collection_list(): void
    {
        $csv = "Tahsilat Tarihi,Tutar,Müvekkil/Unvan\n2026-03-10,5000,GSD Varlık\n";

        $response = $this->actingAs($this->admin)->post('/tahsilat/izlence-gecmis/import', [
            'file' => UploadedFile::fake()->createWithContent('izlence.csv', $csv),
        ], ['Accept' => 'application/json']);

        $response->assertOk()
            ->assertJsonPath('inserted', 1);

        $this->actingAs($this->admin)->getJson('/tahsilat/list')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $dashboard = $this->actingAs($this->admin)->getJson('/tahsilat/dashboard-data')->json();
        $this->assertGreaterThan(0, $dashboard['aylik_tahsilat_tutari']);
    }

    public function test_prim_pivot_only_uses_real_approved_collections(): void
    {
        $protokol = $this->createProtocolWithEqualShare();
        $tahsilatId = $this->createPendingTahsilat($protokol['id'], $protokol['muvekkil_id'], 'pesinat', '100.00');
        $this->actingAs($this->admin)->postJson('/tahsilat/'.$tahsilatId.'/onayla')->assertOk();

        $csv = "Tahsilat Tarihi,Tutar,Müvekkil/Unvan\n2026-03-27,200,GSD Varlık\n";
        $this->actingAs($this->admin)->post('/tahsilat/izlence-gecmis/import', [
            'file' => UploadedFile::fake()->createWithContent('izlence.csv', $csv),
        ], ['Accept' => 'application/json'])->assertOk();

        $pivot = $this->actingAs($this->admin)->getJson('/tahsilat/prim/pivot-table?ay=3&yil=2026')
            ->assertOk()
            ->json();

        $toplam = collect($pivot['prime_esas_pivot_data'])->sum('toplam_prime_esas_tahsilat');
        $this->assertSame(100.0, (float) $toplam);
    }

    public function test_admin_permission_endpoints_are_not_captured_by_dynamic_tahsilat_routes(): void
    {
        $this->actingAs($this->admin)->getJson('/tahsilat/yetki')
            ->assertOk()
            ->assertJsonStructure(['kullanicilar', 'tab_tanimlari']);

        $this->actingAs($this->admin)->getJson('/tahsilat/yetki/prim-ayarlar')
            ->assertOk()
            ->assertJsonStructure(['kademeler', 'hacizci_kademeleri', 'muvekkil_oranlari']);
    }

    public function test_non_admin_only_sees_tabs_granted_by_tab_permissions(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        TahsilatYetkiliKullanici::query()->create([
            'user_id' => $user->id,
            'tahsilat_olusturabilir' => false,
            'protokol_olusturabilir' => false,
            'protokol_duzenleyebilir' => false,
            'toplu_protokol_ekleyebilir' => false,
            'tahsilat_takip_sorumlusu' => false,
            'aktif' => true,
            'tab_permissions' => [
                'dashboard' => false,
                'tahsilat' => false,
                'tum_tahsilatlar' => false,
                'protokol' => false,
                'prim' => true,
            ],
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();

        $this->assertSame(
            ['prim'],
            $response->viewData('visibleTabs')->pluck('key')->all(),
        );
    }

    public function test_tab_permission_is_enforced_for_dashboard_and_prim_endpoints(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        TahsilatYetkiliKullanici::query()->create([
            'user_id' => $user->id,
            'tahsilat_olusturabilir' => false,
            'protokol_olusturabilir' => false,
            'protokol_duzenleyebilir' => false,
            'toplu_protokol_ekleyebilir' => false,
            'tahsilat_takip_sorumlusu' => false,
            'aktif' => true,
            'tab_permissions' => [
                'dashboard' => false,
                'tahsilat' => false,
                'tum_tahsilatlar' => false,
                'protokol' => false,
                'prim' => false,
            ],
        ]);

        $this->actingAs($user)->getJson('/tahsilat/dashboard-data')->assertForbidden();
        $this->actingAs($user)->getJson('/tahsilat/prim/pivot-table')->assertForbidden();

        $user->yetkiKaydi()->update([
            'tab_permissions' => [
                'dashboard' => true,
                'tahsilat' => false,
                'tum_tahsilatlar' => false,
                'protokol' => false,
                'prim' => true,
            ],
        ]);

        $user->refresh();

        $this->actingAs($user)->getJson('/tahsilat/dashboard-data')->assertOk();
        $this->actingAs($user)->getJson('/tahsilat/prim/pivot-table')->assertOk();
    }

    public function test_seeded_prim_settings_only_include_three_kademes(): void
    {
        $ayarlar = $this->actingAs($this->admin)
            ->getJson('/tahsilat/yetki/prim-ayarlar')
            ->assertOk()
            ->json();

        $this->assertCount(3, $ayarlar['kademeler']);
        $this->assertSame(
            ['kademe_1', 'kademe_2', 'kademe_3'],
            collect($ayarlar['kademeler'])->pluck('kademe')->values()->all(),
        );
        $this->assertFalse(
            collect($ayarlar['hacizci_kademeleri'])->contains(fn (array $hacizci) => $hacizci['kademe'] === 'kademe_4')
        );
        $this->assertFalse(
            collect($ayarlar['kademe_pay_oranlari'])->contains(fn (array $oran) => in_array('kademe_4', [$oran['ust_kademe'], $oran['alt_kademe']], true))
        );
    }

    public function test_lookup_seeder_removes_unknown_hacizciler(): void
    {
        $this->assertDatabaseMissing('hacizciler', [
            'ad_soyad' => 'Ahmet Yılmaz',
        ]);
    }

    public function test_prim_pivot_uses_kademe_ratio_for_same_haciz_type(): void
    {
        $muvekkil = Muvekkil::query()->where('ad', 'GSD Varlık')->firstOrFail();
        MuvekkilPrimOrani::query()->updateOrCreate(
            ['muvekkil_id' => $muvekkil->id],
            ['prim_orani' => '10.00', 'aktif' => true],
        );

        $hacizciler = Hacizci::query()->take(2)->get()->values();
        $hacizciler[0]->update(['kademe' => 'kademe_1']);
        $hacizciler[1]->update(['kademe' => 'kademe_3']);

        $response = $this->actingAs($this->admin)->postJson('/tahsilat/protokol/store', [
            'muvekkil_id' => $muvekkil->id,
            'portfoy_id' => null,
            'protokol_tarihi' => '2026-03-27',
            'borclu_adi' => 'Ali Veli',
            'borclu_tckn_vkn' => '12345678901',
            'muhatap_adi' => 'Mehmet Yetkili',
            'muhatap_telefon' => '05550000000',
            'pesinat' => '100.00',
            'toplam_protokol_tutari' => '100.00',
            'hacizciler' => [
                ['hacizci_id' => $hacizciler[0]->id, 'haciz_turu' => 'istihkakli', 'pay_orani' => null],
                ['hacizci_id' => $hacizciler[1]->id, 'haciz_turu' => 'istihkakli', 'pay_orani' => null],
            ],
            'taksitler' => [],
        ])->assertOk();

        $protokol = $response->json('protokol');
        $tahsilatId = $this->createPendingTahsilat($protokol['id'], $muvekkil->id, 'pesinat', '100.00');
        $this->actingAs($this->admin)->postJson('/tahsilat/'.$tahsilatId.'/onayla')->assertOk();

        $pivot = $this->actingAs($this->admin)->getJson('/tahsilat/prim/pivot-table?ay=3&yil=2026')
            ->assertOk()
            ->json('prime_esas_pivot_data');

        $ustSatir = collect($pivot)->firstWhere('hacizci_id', (string) $hacizciler[0]->id);
        $altSatir = collect($pivot)->firstWhere('hacizci_id', (string) $hacizciler[1]->id);

        $this->assertSame(80.0, (float) $ustSatir['toplam_prime_esas_tahsilat']);
        $this->assertSame(8.0, (float) $ustSatir['toplam_prim_tutari']);
        $this->assertSame(20.0, (float) $altSatir['toplam_prime_esas_tahsilat']);
        $this->assertSame(2.0, (float) $altSatir['toplam_prim_tutari']);
    }

    public function test_prim_pivot_splits_equally_when_haciz_types_differ(): void
    {
        $muvekkil = Muvekkil::query()->where('ad', 'GSD Varlık')->firstOrFail();
        MuvekkilPrimOrani::query()->updateOrCreate(
            ['muvekkil_id' => $muvekkil->id],
            ['prim_orani' => '10.00', 'aktif' => true],
        );

        $hacizciler = Hacizci::query()->skip(2)->take(2)->get()->values();
        $hacizciler[0]->update(['kademe' => 'kademe_1']);
        $hacizciler[1]->update(['kademe' => 'kademe_3']);

        $response = $this->actingAs($this->admin)->postJson('/tahsilat/protokol/store', [
            'muvekkil_id' => $muvekkil->id,
            'portfoy_id' => null,
            'protokol_tarihi' => '2026-03-27',
            'borclu_adi' => 'Veli Ali',
            'borclu_tckn_vkn' => '12345678901',
            'muhatap_adi' => 'Mehmet Yetkili',
            'muhatap_telefon' => '05550000000',
            'pesinat' => '100.00',
            'toplam_protokol_tutari' => '100.00',
            'hacizciler' => [
                ['hacizci_id' => $hacizciler[0]->id, 'haciz_turu' => 'istihkakli', 'pay_orani' => null],
                ['hacizci_id' => $hacizciler[1]->id, 'haciz_turu' => '97', 'pay_orani' => null],
            ],
            'taksitler' => [],
        ])->assertOk();

        $protokol = $response->json('protokol');
        $tahsilatId = $this->createPendingTahsilat($protokol['id'], $muvekkil->id, 'pesinat', '100.00');
        $this->actingAs($this->admin)->postJson('/tahsilat/'.$tahsilatId.'/onayla')->assertOk();

        $pivot = $this->actingAs($this->admin)->getJson('/tahsilat/prim/pivot-table?ay=3&yil=2026')
            ->assertOk()
            ->json('prime_esas_pivot_data');

        $ilkSatir = collect($pivot)->firstWhere('hacizci_id', (string) $hacizciler[0]->id);
        $ikinciSatir = collect($pivot)->firstWhere('hacizci_id', (string) $hacizciler[1]->id);

        $this->assertSame(50.0, (float) $ilkSatir['toplam_prime_esas_tahsilat']);
        $this->assertSame(5.0, (float) $ilkSatir['toplam_prim_tutari']);
        $this->assertSame(50.0, (float) $ikinciSatir['toplam_prime_esas_tahsilat']);
        $this->assertSame(5.0, (float) $ikinciSatir['toplam_prim_tutari']);
    }

    public function test_seeded_user_accounts_exist_with_expected_passwords_and_roles(): void
    {
        $expectedUsers = [
            'sumervarlik@local.test' => ['password' => 'tahsilat', 'is_admin' => false],
            'emirvarlik@local.test' => ['password' => 'tahsilat', 'is_admin' => false],
            'gelecekvarlik@local.test' => ['password' => 'tahsilat', 'is_admin' => false],
            'dogruvarlik@local.test' => ['password' => 'tahsilat', 'is_admin' => false],
            'birlesimvarlik@local.test' => ['password' => 'tahsilat', 'is_admin' => false],
            'birikimvarlik@local.test' => ['password' => 'tahsilat', 'is_admin' => false],
            'dengevarlik@local.test' => ['password' => 'tahsilat', 'is_admin' => false],
            'gsdvarlik@local.test' => ['password' => 'tahsilat', 'is_admin' => false],
            'toprak@local.test' => ['password' => '032e1ae5', 'is_admin' => true],
            'ademkaratepe@local.test' => ['password' => '032e1ae5', 'is_admin' => true],
        ];

        foreach ($expectedUsers as $email => $expectation) {
            $user = User::query()->where('email', $email)->first();

            $this->assertNotNull($user, sprintf('Seeded user %s was not created.', $email));
            $this->assertSame($expectation['is_admin'], $user->isAdmin());
            $this->assertTrue(Hash::check($expectation['password'], $user->password));

            $yetki = $user->yetkiKaydi;
            $this->assertNotNull($yetki, sprintf('Seeded user %s does not have a permission row.', $email));
            $this->assertTrue((bool) $yetki->tahsilat_olusturabilir);
            $this->assertTrue((bool) $yetki->protokol_olusturabilir);
            $this->assertTrue((bool) $yetki->protokol_duzenleyebilir);
            $this->assertTrue((bool) $yetki->toplu_protokol_ekleyebilir);
            $this->assertTrue((bool) $yetki->tahsilat_takip_sorumlusu);
            $this->assertTrue(collect($user->tabPermissions())->every(fn (bool $allowed) => $allowed));
        }
    }

    public function test_protocols_cannot_be_deleted_from_application_code(): void
    {
        $protokol = Protokol::query()->findOrFail($this->createProtocol()['id']);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Protokol kayitlari uygulama icinden silinemez.');

        $protokol->delete();
    }

    public function test_collections_cannot_be_deleted_from_application_code(): void
    {
        $protokol = $this->createProtocol();
        $tahsilatId = $this->createPendingTahsilat($protokol['id'], $protokol['muvekkil_id'], 'pesinat', '100.00');
        $tahsilat = Tahsilat::query()->findOrFail($tahsilatId);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Tahsilat kayitlari uygulama icinden silinemez.');

        $tahsilat->delete();
    }

    private function createProtocol(): array
    {
        $muvekkil = Muvekkil::query()->firstOrFail();
        $hacizci = Hacizci::query()->firstOrFail();

        $response = $this->actingAs($this->admin)->postJson('/tahsilat/protokol/store', [
            'muvekkil_id' => $muvekkil->id,
            'portfoy_id' => null,
            'protokol_tarihi' => '2026-03-27',
            'borclu_adi' => 'Ali Veli',
            'borclu_tckn_vkn' => '12345678901',
            'muhatap_adi' => 'Mehmet Yetkili',
            'muhatap_telefon' => '05550000000',
            'pesinat' => '1000.00',
            'toplam_protokol_tutari' => '1000.00',
            'hacizciler' => [
                ['hacizci_id' => $hacizci->id, 'haciz_turu' => 'istihkakli', 'pay_orani' => null],
            ],
            'taksitler' => [],
        ])->assertOk();

        return $response->json('protokol');
    }

    private function createProtocolWithSingleInstallment(): array
    {
        $muvekkil = Muvekkil::query()->firstOrFail();
        $hacizci = Hacizci::query()->firstOrFail();

        $response = $this->actingAs($this->admin)->postJson('/tahsilat/protokol/store', [
            'muvekkil_id' => $muvekkil->id,
            'portfoy_id' => null,
            'protokol_tarihi' => '2026-03-27',
            'borclu_adi' => 'Ali Veli',
            'borclu_tckn_vkn' => '12345678901',
            'muhatap_adi' => 'Mehmet Yetkili',
            'muhatap_telefon' => '05550000000',
            'pesinat' => '0.00',
            'toplam_protokol_tutari' => '100.00',
            'hacizciler' => [
                ['hacizci_id' => $hacizci->id, 'haciz_turu' => 'istihkakli', 'pay_orani' => null],
            ],
            'taksitler' => [
                ['taksit_tarihi' => '2026-04-10', 'taksit_tutari' => '100.00'],
            ],
        ])->assertOk();

        $protokol = $response->json('protokol');
        $protokol['taksit_id'] = $protokol['taksitler'][0]['id'];

        return $protokol;
    }

    private function createProtocolWithEqualShare(): array
    {
        $muvekkil = Muvekkil::query()->where('ad', 'GSD Varlık')->firstOrFail();
        $hacizciler = Hacizci::query()->take(2)->get();

        $response = $this->actingAs($this->admin)->postJson('/tahsilat/protokol/store', [
            'muvekkil_id' => $muvekkil->id,
            'portfoy_id' => null,
            'protokol_tarihi' => '2026-03-27',
            'borclu_adi' => 'Ali Veli',
            'borclu_tckn_vkn' => '12345678901',
            'muhatap_adi' => 'Mehmet Yetkili',
            'muhatap_telefon' => '05550000000',
            'pesinat' => '100.00',
            'toplam_protokol_tutari' => '100.00',
            'hacizciler' => [
                ['hacizci_id' => $hacizciler[0]->id, 'haciz_turu' => 'istihkakli', 'pay_orani' => null],
                ['hacizci_id' => $hacizciler[1]->id, 'haciz_turu' => '97', 'pay_orani' => null],
            ],
            'taksitler' => [],
        ])->assertOk();

        return $response->json('protokol');
    }

    private function createPendingTahsilat(string $protokolId, string $muvekkilId, string $odemeKalemi, string $tutar): string
    {
        $response = $this->actingAs($this->admin)->post('/tahsilat/store', [
            'protokolsuz' => '0',
            'protokol_id' => $protokolId,
            'odeme_kalemi' => $odemeKalemi,
            'muvekkil_id' => $muvekkilId,
            'borclu_adi' => 'Ali Veli',
            'borclu_tckn_vkn' => '12345678901',
            'tahsilat_tarihi' => '2026-03-27',
            'tutar' => $tutar,
            'tahsilat_yontemi' => 'elden_alindi',
            'tahsilat_birimleri' => ['sulhen'],
            'dekont' => UploadedFile::fake()->create('dekont.pdf', 20, 'application/pdf'),
        ], ['Accept' => 'application/json'])->assertOk();

        return $response->json('tahsilat.id');
    }
}
