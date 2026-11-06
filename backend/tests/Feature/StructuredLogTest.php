<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StructuredLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_gagal_dicatat_tanpa_email_atau_password(): void
    {
        Log::spy();
        User::factory()->create(['email' => 'guru@test.com', 'password' => bcrypt('rahasia123')]);

        $this->postJson('/api/login', ['email' => 'guru@test.com', 'password' => 'salah-banget'])
            ->assertStatus(401);

        Log::shouldHaveReceived('warning')->withArgs(function ($message, $context = []) {
            $json = json_encode($context);

            return $message === 'auth.login_failed'
                && ! str_contains($json, 'salah-banget')
                && ! str_contains($json, 'guru@test.com');
        })->once();
    }

    public function test_validasi_gagal_dicatat_dengan_nama_field_saja(): void
    {
        Log::spy();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/books', ['author' => 'Ra'])->assertStatus(422);

        Log::shouldHaveReceived('warning')->withArgs(function ($message, $context = []) {
            return $message === 'validation.failed'
                && $context['invalid_fields'] === ['title'];
        })->once();
    }

    public function test_simpan_buku_dicatat_dengan_konteks(): void
    {
        Log::spy();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/books', ['title' => 'IPS Kelas 8', 'author' => 'Ra'])->assertStatus(201);

        Log::shouldHaveReceived('info')->withArgs(function ($message, $context = []) use ($user) {
            return $message === 'book.created'
                && $context['user_id'] === $user->id
                && isset($context['book_id'], $context['action'], $context['reason']);
        })->once();
    }
}
