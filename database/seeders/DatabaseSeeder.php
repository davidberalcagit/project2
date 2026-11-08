<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        fake()->unique(true);
        fake()->seed(20261106);

        $this->call([RolePermissionSeeder::class]);
        $this->call([AdminUserSeeder::class]);
        $this->call([UserSeeder::class]);
        $this->call([CategorySeeder::class]);
        $this->call([AuthorSeeder::class]);
        $this->call([BookSeeder::class]);
        $this->call([LibrarySeeder::class]);
        $this->call([CopyStatusSeeder::class]);
        $this->call([CopieSeeder::class]);
        $this->call([BorrowStatusSeeder::class]);
        $this->call([BorrowSeeder::class]);
        $this->call([ReserveSeeder::class]);
        $this->call([FavoriteSeeder::class]);
        $this->call([OpinionSeeder::class]);

    }
}
