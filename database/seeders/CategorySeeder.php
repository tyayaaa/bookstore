<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $batchSize = 1000;
        $total = 5000;

        for ($i = 0; $i < $total; $i += $batchSize) {
            $data = [];

            for ($j = 0; $j < $batchSize; $j++) {
                $data[] = [
                    'name' => fake()->word() . '_' . fake()->unique()->numberBetween(1, $total * 10),
                    'description' => fake()->sentence(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Category::insert($data);
        }
    }
}
