<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_guests_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_dashboard_renders_for_authenticated_user(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('is_admin', true)->firstOrFail();

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Tahsilat Yönetimi')
            ->assertSee('Protokol ve tahsilat merkezi');
    }
}
