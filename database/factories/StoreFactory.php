<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'    => $this->faker->company() . ' Bookstore',
            'address' => $this->faker->streetAddress(),
            'city'    => $this->faker->city(),
            'phone'   => $this->faker->phoneNumber(),
        ];
    }
}
