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
        Book::factory()->create([
            'title' => 'Proyecto Hail Mary',
            'editorial' => 'Editorial Nova',
            'year' => '2021',
            'edition' => '1º',
            'isbn' => '9788418037188',
        ]);

        Book::factory()->create([
            'title' => 'La Patocracia',
            'editorial' => 'Editorial',
            'year' => '2019',
            'edition' => '1º',
            'isbn' => '978000000001',
        ]);

        Book::factory()->create([
            'title' => 'Cien años de soledad',
            'editorial' => 'Editorial Sudamericana',
            'year' => '1967',
            'edition' => '1º',
            'isbn' => '978030747472',
        ]);

        Book::factory()->create([
            'title' => '1984',
            'editorial' => 'Secker & Warburg',
            'year' => '1949',
            'edition' => '1º',
            'isbn' => '978045152495',
        ]);

        Book::factory()->create([
            'title' => 'El principito',
            'editorial' => 'Reynal & Hitchcock',
            'year' => '1943',
            'edition' => '1º',
            'isbn' => '9780156012195',
        ]);

        Book::factory()->create([
            'title' => 'Don Quijote de la Mancha',
            'editorial' => 'Francisco de Robles',
            'year' => '1605',
            'edition' => '1º',
            'isbn' => '9788424115913',
        ]);

        Book::factory()->create([
            'title' => 'Fahrenheit 451',
            'editorial' => 'Ballantine Books',
            'year' => '1953',
            'edition' => '1º',
            'isbn' => '9781451678185',
        ]);

        Book::factory()->create([
            'title' => 'Dune',
            'editorial' => 'Chilton Company',
            'year' => '1965',
            'edition' => '1º',
            'isbn' => '9780441172719',
        ]);

        Book::factory()->create([
            'title' => 'El nombre del viento',
            'editorial' => 'DAW Books',
            'year' => '2007',
            'edition' => '1º',
            'isbn' => '9780756404741',
        ]);

        Book::factory()->create([
            'title' => 'Crónica de una muerte anunciada',
            'editorial' => 'Editorial Sudamericana',
            'year' => '1981',
            'edition' => '1º',
            'isbn' => '9780307386853',
        ]);
    }
}
