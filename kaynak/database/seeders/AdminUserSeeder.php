<?php

namespace Database\Seeders;

use App\Models\TahsilatYetkiliKullanici;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $tabPermissions = collect(config('tahsilat.tab_tanimlari', []))
            ->pluck('key')
            ->mapWithKeys(fn (string $key) => [$key => true])
            ->all();

        foreach ($this->seededUsers() as $account) {
            $user = User::query()
                ->where('email', $account['email'])
                ->when(
                    ! empty($account['legacy_emails'] ?? []),
                    fn ($query) => $query->orWhereIn('email', $account['legacy_emails'])
                )
                ->first() ?? new User();

            $user->fill([
                'name' => $account['name'],
                'email' => $account['email'],
                'password' => Hash::make($account['password']),
                'is_admin' => $account['is_admin'],
            ]);
            $user->email_verified_at = now();
            $user->save();

            TahsilatYetkiliKullanici::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'tahsilat_olusturabilir' => true,
                    'protokol_olusturabilir' => true,
                    'protokol_duzenleyebilir' => true,
                    'toplu_protokol_ekleyebilir' => true,
                    'tahsilat_takip_sorumlusu' => true,
                    'aktif' => true,
                    'tab_permissions' => $tabPermissions,
                ],
            );
        }
    }

    private function seededUsers(): array
    {
        return [
            [
                'name' => 'Sumer Varlik',
                'email' => 'sumervarlik@local.test',
                'password' => 'tahsilat',
                'is_admin' => false,
            ],
            [
                'name' => 'Emir Varlik',
                'email' => 'emirvarlik@local.test',
                'password' => 'tahsilat',
                'is_admin' => false,
            ],
            [
                'name' => 'Gelecek Varlik',
                'email' => 'gelecekvarlik@local.test',
                'password' => 'tahsilat',
                'is_admin' => false,
            ],
            [
                'name' => 'Dogru Varlik',
                'email' => 'dogruvarlik@local.test',
                'password' => 'tahsilat',
                'is_admin' => false,
            ],
            [
                'name' => 'Birlesim Varlik',
                'email' => 'birlesimvarlik@local.test',
                'password' => 'tahsilat',
                'is_admin' => false,
            ],
            [
                'name' => 'Birikim Varlik',
                'email' => 'birikimvarlik@local.test',
                'password' => 'tahsilat',
                'is_admin' => false,
            ],
            [
                'name' => 'Denge Varlik',
                'email' => 'dengevarlik@local.test',
                'password' => 'tahsilat',
                'is_admin' => false,
            ],
            [
                'name' => 'GSD Varlik',
                'email' => 'gsdvarlik@local.test',
                'password' => 'tahsilat',
                'is_admin' => false,
            ],
            [
                'name' => 'Toprak',
                'email' => 'toprak@local.test',
                'password' => '032e1ae5',
                'is_admin' => true,
            ],
            [
                'name' => 'Adem Karatepe',
                'email' => 'ademkaratepe@local.test',
                'password' => '032e1ae5',
                'is_admin' => true,
                'legacy_emails' => ['adamkaratepe@local.test'],
            ],
            [
                'name' => 'admin',
                'email' => 'admin@local.test',
                'password' => 'admin4434',
                'is_admin' => true,
                'legacy_emails' => ['admin@local.test'],
            ],
        ];
    }
}
