<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Author;
use App\Models\Category;

class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'author_id' => Author::inRandomOrder()->value('id') ?? Author::factory(),
            'category_id' => Category::inRandomOrder()->value('id') ?? Category::factory(),
            'published_year' => $this->faker->year(),
            'isbn' => $this->faker->unique()->isbn13(),
            'stock' => $this->faker->numberBetween(1, 100),
        ];
    }
}
