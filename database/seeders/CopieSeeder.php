<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Copie;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CopieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = Book::all();
        foreach ($books as $book) {
            Copie::factory()->create([
                'book_id' => $book-> id,
                'barcode' =>  random_int(20,100000) . $book-> id
            ]);
        }
    }
}
