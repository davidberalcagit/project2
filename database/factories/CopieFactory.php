<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Copie;
use App\Models\Library;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Copie>
 */
class CopieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'library_id' => Library::factory(),
            'barcode' => fake()->unique()->numerify('LIB-########'),
        ];
    }
}
