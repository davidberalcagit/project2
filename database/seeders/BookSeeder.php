<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Book::create([
            'title' => 'Proyecto Hail Mary',
            'editorial' => 'Editorial Nova',
            'year' => '2021',
            'edition' => '1º',
            'isbn' => '978841803718'
        ]);
        Book::factory(5)->create();
    }
}
