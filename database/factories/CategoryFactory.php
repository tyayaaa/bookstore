<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->word()) . ' ' . $this->faker->unique()->numberBetween(1, 10000),
            'description' => $this->faker->sentence(),
        ];
    }
}
