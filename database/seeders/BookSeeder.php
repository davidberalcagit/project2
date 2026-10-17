<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reutiliza registros existentes para no crear categorías ni autores adicionales.
        Book::factory(6)->recycle(Category::all())->recycle(Author::all())->create();

        // Deja cuatro libros sin relaciones con categorías ni autores.
        Book::factory(4)->create(['category_id' => null, 'author_id' => null]);
    }
}
