<?php

use App\Models\Book;
use App\Models\Copie;
use App\Models\Library;
use App\Models\Opinion;
use App\Services\BookCatalogService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

it('lists books available at a selected library above the minimum rating', function (): void {
    $library = Library::factory()->create();
    $matchingBook = Book::factory()->create(['title' => 'Libro coincidente']);
    $unavailableBook = Book::factory()->create(['title' => 'Libro no disponible']);
    Copie::factory()->for($matchingBook)->for($library)->available()->create();
    Opinion::factory()->for($matchingBook)->create(['rating' => 5]);
    Opinion::factory()->for($unavailableBook)->create(['rating' => 5]);

    $books = app(BookCatalogService::class)->listing([
        'library_id' => $library->id,
        'min_rating' => 4,
    ]);

    expect($books->getCollection()->modelKeys())->toBe([$matchingBook->id])
        ->and($books->getCollection()->first()->opinions_avg_rating)->toBe(5.0);
});

it('returns the latest featured books up to the requested limit', function (): void {
    $olderBook = Book::factory()->create(['created_at' => now()->subDay()]);
    $newerBook = Book::factory()->create(['created_at' => now()]);

    $books = app(BookCatalogService::class)->featured(1);

    expect($books->modelKeys())->toBe([$newerBook->id])
        ->and($olderBook->id)->not->toBe($newerBook->id);
});
