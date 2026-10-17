<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Copie;
use App\Models\Library;
use Illuminate\Database\Seeder;

class CopieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Book::query()->each(function (Book $book): void {
            Copie::factory()
                ->for($book)
                ->for(Library::query()->inRandomOrder()->firstOrFail())
                ->available()
                ->create();
        });
    }
}
