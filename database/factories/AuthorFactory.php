<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AuthorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'country' => $this->faker->country(),
            'bio' => $this->faker->paragraph(),
        ];
    }
}
