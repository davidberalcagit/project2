<?php

use App\Models\Book;
use App\Models\Copie;
use App\Models\CopyStatus;
use App\Models\Library;
use Database\Seeders\CopieSeeder;
use Database\Seeders\CopyStatusSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

it('seeds the supported copy statuses in English', function () {
    $this->seed(CopyStatusSeeder::class);
    $this->seed(CopyStatusSeeder::class);

    $this->assertDatabaseCount('copy_statuses', 5);

    expect(CopyStatus::query()->orderBy('id')->pluck('name')->all())->toBe([
        'Available',
        'Reserved',
        'Borrowed',
        'Expired',
        'Unavailable',
    ]);
});

it('associates a copy with its status through factories', function () {
    $copy = Copie::factory()->create();

    expect($copy->copyStatus->name)->toBeIn([
        'Available',
        'Reserved',
        'Borrowed',
        'Expired',
        'Unavailable',
    ])
        ->and($copy->copyStatus->copies->modelKeys())->toBe([$copy->id]);
});

it('seeds each existing book as an available copy in an existing library', function () {
    $book = Book::factory()->create();
    $library = Library::factory()->create();

    $this->seed(CopieSeeder::class);

    $this->assertDatabaseCount('copies', 1);

    $copy = Copie::query()->firstOrFail();

    expect($copy->book->is($book))->toBeTrue()
        ->and($copy->library->is($library))->toBeTrue()
        ->and($copy->copyStatus->name)->toBe('Available');
});
