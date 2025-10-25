<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Author;
use App\Models\Category;
use App\Models\Store;
use Faker\Factory as Faker;

class BookSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $authors    = Author::pluck('id')->toArray();
        $categories = Category::pluck('id')->toArray();
        $stores     = Store::pluck('id')->toArray();

        $total = 100000;   // total data yang mau disimpan
        $batchSize = 1000; // jumlah per batch

        $bar = $this->command->getOutput()->createProgressBar($total);
        $bar->start();

        DB::disableQueryLog();

        for ($i = 0; $i < $total; $i += $batchSize) {
            $data = [];

            for ($j = 0; $j < $batchSize && ($i + $j) < $total; $j++) {
                $data[] = [
                    'author_id'      => $authors[array_rand($authors)],
                    'category_id'    => $categories[array_rand($categories)],
                    'store_id'       => $stores[array_rand($stores)],
                    'publisher'      => $faker->company(),
                    'published_year' => $faker->numberBetween(1980, 2025),
                    'isbn'           => $faker->isbn13(),
                    'title'          => $faker->sentence(4),
                    'stock'          => $faker->numberBetween(0, 100),
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }

            DB::table('books')->insert($data);
            $bar->advance(count($data));
            unset($data);
        }

        $bar->finish();
    }
}
