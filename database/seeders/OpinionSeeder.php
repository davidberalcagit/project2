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
        $missingUsers = 2 - User::query()->count();

        if ($missingUsers > 0) {
            User::factory($missingUsers)->create();
        }

        $missingBooks = 5 - Book::query()->count();

        if ($missingBooks > 0) {
            Book::factory($missingBooks)->create();
        }

        $books = Book::query()->orderBy('id')->get();

        foreach ($books as $book) {
            $missingOpinions = 2 - $book->opinions()->count();

            if ($missingOpinions <= 0) {
                continue;
            }

            $reviewers = User::query()
                ->whereDoesntHave('opinions', fn ($query) => $query->where('book_id', $book->id))
                ->take($missingOpinions)
                ->get();

            if ($reviewers->count() < $missingOpinions) {
                $reviewers = $reviewers->concat(
                    User::factory($missingOpinions - $reviewers->count())->create(),
                );
            }

            foreach ($reviewers as $index => $reviewer) {
                $rating = fake()->numberBetween(1, 5);

                Opinion::query()->firstOrCreate(
                    [
                        'user_id' => $reviewer->id,
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
}
