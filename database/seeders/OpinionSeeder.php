<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Opinion;
use App\Models\User;
use Illuminate\Database\Seeder;

class OpinionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $missingUsers = 5 - User::query()->count();

        if ($missingUsers > 0) {
            User::factory($missingUsers)->create();
        }

        $missingBooks = 5 - Book::query()->count();

        if ($missingBooks > 0) {
            Book::factory($missingBooks)->create();
        }

        $books = Book::query()->orderBy('id')->take(5)->get();

        foreach ($books as $index => $book) {
            $user = User::query()->orderBy('id')->skip($index)->firstOrFail();
            $rating = $index + 1;

            Opinion::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                ],
                [
                    'rating' => $rating,
                    'comment' => "Opinion de ejemplo {$rating}.",
                ],
            );
        }
    }
}
