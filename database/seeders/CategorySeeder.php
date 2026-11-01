<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Fantasía',
            'Ciencia ficción',
            'Misterio',
            'Romance',
            'Terror',
            'Aventura',
            'Drama',
            'Historia',
        ];

        foreach ($categories as $name) {
            Category::query()->firstOrCreate(
                ['name' => $name],
                ['description' => "Libros de {$name}."]
            );
        }
    }
}
