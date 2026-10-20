<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_berhasil_mengembalikan_token(): void
    {
        User::factory()->create(['email' => 'guru@test.com', 'password' => bcrypt('rahasia123')]);

        $this->postJson('/api/login', ['email' => 'guru@test.com', 'password' => 'rahasia123'])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
    }

    public function test_login_password_salah_mengembalikan_401(): void
    {
        User::factory()->create(['email' => 'guru@test.com', 'password' => bcrypt('rahasia123')]);

        $this->postJson('/api/login', ['email' => 'guru@test.com', 'password' => 'salah'])
            ->assertStatus(401);
    }

    public function test_login_tanpa_data_mengembalikan_422(): void
    {
        $this->postJson('/api/login', [])->assertStatus(422);
    }

    public function test_logout_menghapus_token(): void
    {
        User::factory()->create(['email' => 'guru@test.com', 'password' => bcrypt('rahasia123')]);
        $token = $this->postJson('/api/login', ['email' => 'guru@test.com', 'password' => 'rahasia123'])
            ->json('token');

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
