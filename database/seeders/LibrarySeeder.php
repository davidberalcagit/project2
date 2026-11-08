<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Library;
use Illuminate\Database\Seeder;

class LibrarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $libraryCount = Book::query()->count();

        for ($libraryNumber = 0; $libraryNumber < $libraryCount; $libraryNumber++) {
            Library::factory()->create([
                'name' => fake()->name,
                'location' => fake()->city(),
            ]);
        }
    }
}
