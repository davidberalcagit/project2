<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bookIds = Book::query()->pluck('id');
        User::query()->each(fn (User $user) => $user->favoriteBooks()->syncWithoutDetaching($bookIds));
    }
}
