<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Copie;
use App\Models\Library;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LibrarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = Book::all();
        foreach ($books as $book) {
            Library::factory()->create([
                'name' => fake()->name,
                'location' => fake()->city(),

            ]);
        }
    }
}
