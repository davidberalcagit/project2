<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\Borrow;
use App\Models\Category;
use App\Models\Copie;
use App\Models\Opinion;
use App\Models\Reserve;
use App\Models\User;
use Tests\TestCase;

uses(TestCase::class);

it('rebuilds the same complete database with migrate fresh and DatabaseSeeder', function () {
    $this->travelTo('2026-11-06 12:00:00');

    $seedAndSnapshot = function (): array {
        $this->artisan('migrate:fresh', ['--seed' => true, '--force' => true])
            ->assertSuccessful();

        return [
            'categories' => Category::query()->orderBy('id')->pluck('name')->all(),
            'authors' => Author::query()->orderBy('id')->pluck('name')->all(),
            'users' => User::query()->orderBy('id')->pluck('email')->all(),
            'books' => Book::query()->orderBy('id')->pluck('title')->all(),
            'copies' => Copie::query()
                ->with('copyStatus')
                ->orderBy('id')
                ->get()
                ->map(fn (Copie $copy): array => [$copy->barcode, $copy->copyStatus->name])
                ->all(),
            'borrows' => Borrow::query()
                ->orderBy('id')
                ->get(['user_id', 'copy_id', 'loaned_at', 'deadline', 'borrow_status_id'])
                ->toArray(),
            'reserves' => Reserve::query()
                ->orderBy('id')
                ->get(['user_id', 'copy_id', 'start_date', 'expiration_date'])
                ->toArray(),
            'opinions' => Opinion::query()
                ->orderBy('id')
                ->get(['user_id', 'book_id', 'rating', 'comment'])
                ->toArray(),
        ];
    };

    $firstSnapshot = $seedAndSnapshot();
    $secondSnapshot = $seedAndSnapshot();

    expect($secondSnapshot)->toBe($firstSnapshot)
        ->and(count($firstSnapshot['categories']))->toBe(8)
        ->and(count($firstSnapshot['authors']))->toBe(8)
        ->and(count($firstSnapshot['users']))->toBe(6)
        ->and(count($firstSnapshot['books']))->toBe(14)
        ->and(count($firstSnapshot['copies']))->toBe(14)
        ->and(count($firstSnapshot['borrows']))->toBe(5)
        ->and(count($firstSnapshot['reserves']))->toBe(3)
        ->and(count($firstSnapshot['opinions']))->toBe(5);
});
