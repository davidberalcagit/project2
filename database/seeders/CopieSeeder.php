<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Copie;
use App\Models\Library;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CopieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $book = Book::all();
        $librarie = Library::all();

        foreach ($book as $books) {
            foreach ($librarie as $libraries) {

                Copie::factory()->create([
                    'book_id' => $books->id,
                    'library_id' => $libraries->id,
                    'barcode' => random_int(20, 100000) . $book->id,
                ]);
            }
        }
    }
}
