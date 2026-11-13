<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Copie;
use App\Models\Favorite;
use App\Models\Library;
use App\Models\Opinion;
use App\Models\Reserve;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

it('searches book fields and related author and category names', function () {
    $authorBook = Book::factory()
        ->for(Author::factory()->state(['name' => 'Ursula Le Guin']))
        ->create();
    $categoryBook = Book::factory()
        ->for(Category::factory()->state(['name' => 'Historia']))
        ->create();
    $otherBook = Book::factory()->create();

    $authorMatches = Book::search('Ursula')->pluck('id')->all();
    $categoryMatches = Book::search('Historia')->pluck('id')->all();

    expect($authorMatches)->toBe([$authorBook->id])
        ->and($categoryMatches)->toBe([$categoryBook->id])
        ->and($authorMatches)->not->toContain($otherBook->id);
});

it('finds books with an available copy at the requested library', function () {
    $library = Library::factory()->create();
    $otherLibrary = Library::factory()->create();
    $availableBook = Book::factory()->create();
    $borrowedBook = Book::factory()->create();
    $reservedBook = Book::factory()->create();
    $otherLibraryBook = Book::factory()->create();

    $availableCopy = Copie::factory()->for($availableBook, 'book')->for($library)->create();
    $borrowedCopy = Copie::factory()->for($borrowedBook, 'book')->for($library)->create();
    $reservedCopy = Copie::factory()->for($reservedBook, 'book')->for($library)->create();
    Copie::factory()->for($otherLibraryBook, 'book')->for($otherLibrary)->create();

    $user = User::factory()->create();

    $borrowedCopy->borrows()->create([
        'user_id' => $user->id,
        'deadline' => today()->addWeek(),
    ]);

    Reserve::factory()->create([
        'copy_id' => $reservedCopy->id,
        'start_date' => now()->subHour(),
        'expiration_date' => now()->addHour(),
    ]);

    $matchingBookIds = Book::availableAtLibrary($library->id)->pluck('id')->all();

    expect($matchingBookIds)->toBe([$availableBook->id]);
});

it('filters books by their average opinion rating', function () {
    $highlyRatedBook = Book::factory()->create();
    $lowRatedBook = Book::factory()->create();
    $unratedBook = Book::factory()->create();

    Opinion::factory()->for($highlyRatedBook, 'book')->create(['rating' => 5]);
    Opinion::factory()->for($highlyRatedBook, 'book')->create(['rating' => 4]);
    Opinion::factory()->for($lowRatedBook, 'book')->create(['rating' => 3]);
    Opinion::factory()->for($lowRatedBook, 'book')->create(['rating' => 2]);

    $matchingBookIds = Book::withAverageRatingAtLeast(4.5)->pluck('id')->all();

    expect($matchingBookIds)->toBe([$highlyRatedBook->id])
        ->and($unratedBook->id)->not->toBeIn($matchingBookIds);
});

it('orders popular books by favorite count and applies a minimum', function () {
    $popularBook = Book::factory()->create(['title' => 'Popular']);
    $lessPopularBook = Book::factory()->create(['title' => 'Less popular']);

    Favorite::factory()->for($popularBook, 'book')->for(User::factory())->create();
    Favorite::factory()->for($popularBook, 'book')->for(User::factory())->create();
    Favorite::factory()->for($lessPopularBook, 'book')->for(User::factory())->create();

    $popularBookIds = Book::popular(minimumFavorites: 2)->pluck('id')->all();

    expect($popularBookIds)->toBe([$popularBook->id]);
});
