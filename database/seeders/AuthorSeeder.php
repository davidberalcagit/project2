<?php

namespace Database\Seeders;

use App\Models\Author;
use Illuminate\Database\Seeder;

class AuthorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Author::create(['name' => 'Andy Weir', 'biography' => 'Escritor estadounidense de ciencia ficción.']);
        Author::create(['name' => 'Ursula K. Le Guin', 'biography' => 'Escritora de ciencia ficción y fantasía.']);
        Author::create(['name' => 'Carl Sagan', 'biography' => 'Astrónomo y divulgador científico.']);
    }
}
