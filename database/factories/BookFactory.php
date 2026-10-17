<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Model>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'editorial' => fake()->company(),
            'year' => fake()->year(),
            'edition' => '1º',
            'isbn' => fake()->unique()->isbn13(),
            'category_id' => Category::factory(), // Crea una nueva categoría si no existe una relacionada
            'author_id' => Author::factory(), // Crea un nuevo autor si no existe uno relacionado
        ];
    }
}
