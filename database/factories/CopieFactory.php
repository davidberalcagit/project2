<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Copie;
use App\Models\CopyStatus;
use App\Models\Library;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Copie>
 */
class CopieFactory extends Factory
{
    public function available(): static
    {
        return $this->state([
            'copy_status_id' => CopyStatus::factory()->state([
                'name' => 'Available',
            ]),
        ]);
    }

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
            'copy_status_id' => CopyStatus::factory(),
            'barcode' => fake()->unique()->numerify('LIB-########'),
        ];
    }
}
