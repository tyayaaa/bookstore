<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Author;

class AuthorSeeder extends Seeder
{
    public function run(): void
    {
        $batchSize = 1000;
        $total = 10000;

        for ($i = 0; $i < $total; $i += $batchSize) {
            $data = [];

            for ($j = 0; $j < $batchSize; $j++) {
                $data[] = [
                    'name' => fake()->name(),
                    'bio' => fake()->text(200),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Author::insert($data);
        }
    }
}
