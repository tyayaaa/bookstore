<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Book;
use App\Models\User;

class RatingSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create();
        $bookIds = Book::pluck('id')->toArray();
        $userIds = User::pluck('id')->toArray();

        $total = 500_000;
        $batchSize = 5_000;

        $bar = $this->command->getOutput()->createProgressBar($total);
        $bar->start();

        DB::disableQueryLog();

        for ($i = 0; $i < $total; $i += $batchSize) {
            $data = [];

            $limit = min($batchSize, $total - $i);
            for ($j = 0; $j < $limit; $j++) {
                $data[] = [
                    'book_id'    => $bookIds[array_rand($bookIds)],
                    'user_id'    => $userIds[array_rand($userIds)],
                    'rating'     => $faker->numberBetween(1, 10),
                    'comment'    => $faker->sentence(),
                    'created_at' => now()->subDays(rand(0, 60))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
                    'updated_at' => now(),
                ];
            }

            DB::table('ratings')->insert($data);

            $bar->advance(count($data));
            unset($data);
        }

        $bar->finish();
    }
}
