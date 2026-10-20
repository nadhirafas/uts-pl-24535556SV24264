<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_tanpa_token_mengembalikan_401(): void
    {
        $this->getJson('/api/books')->assertStatus(401);
        $this->postJson('/api/books', [])->assertStatus(401);
    }

    public function test_tambah_buku_mengembalikan_201(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/books', ['title' => 'IPS Kelas 8', 'author' => 'Ra', 'year' => 2024])
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'IPS Kelas 8');

        $this->assertDatabaseHas('books', ['title' => 'IPS Kelas 8']);
    }

    public function test_validasi_gagal_mengembalikan_422(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/books', ['author' => 'Ra'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_tampil_ubah_hapus_buku(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $book = Book::factory()->create();

        $this->getJson('/api/books')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson("/api/books/{$book->id}")->assertOk();

        $this->putJson("/api/books/{$book->id}", ['title' => 'Judul Baru', 'author' => 'Ra'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Judul Baru');

        $this->deleteJson("/api/books/{$book->id}")->assertOk();
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
}
