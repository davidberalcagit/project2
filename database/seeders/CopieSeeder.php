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
        foreach ($book as $books) {
            $librarie = Library::inRandomOrder()->first();
            Copie::create([
                    'book_id' => $books->id,
                    'library_id' => $librarie->id,
                    'barcode' => random_int(20, 100000) . $books->id,
                ]);
            }
        }
}
