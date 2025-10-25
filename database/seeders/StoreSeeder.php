<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class StoreSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $stores = [];
        for ($i = 0; $i < 20; $i++) {
            $stores[] = [
                'name'       => $faker->company(),
                'address'    => $faker->address(),
                'city'       => $faker->city(),
                'phone'      => $faker->phoneNumber(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('stores')->insert($stores);
    }
}
