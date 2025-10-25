<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Book;
use App\Models\User;

class RatingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'book_id' => Book::inRandomOrder()->value('id') ?? Book::factory(),
            'user_id' => User::inRandomOrder()->value('id') ?? User::factory(),
            'rating' => $this->faker->numberBetween(1, 10),
            'comment' => $this->faker->sentence(),
        ];
    }
}
