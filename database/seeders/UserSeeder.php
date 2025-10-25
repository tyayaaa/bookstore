<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Tambah 10 user dummy
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => "User $i",
                'email' => "user$i@example.com",
                'password' => Hash::make('password'),
            ]);
        }
    }
}
