<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun login dibuat dari variabel lingkungan (tidak ditulis di repository).
        // firstOrCreate: aman dijalankan berulang setiap container start.
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
            ['name' => 'Admin', 'password' => Hash::make(env('ADMIN_PASSWORD'))]
        );
    }
}
