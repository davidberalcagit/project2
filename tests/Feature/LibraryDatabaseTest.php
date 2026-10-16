<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\Borrow;
use App\Models\Category;
use App\Models\Copie;
use App\Models\Favorite;
use App\Models\Library;
use App\Models\Reserve;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

it('creates every library model with its factory and persists its required relationships', function () {
    $user = User::factory()->create();
    $author = Author::factory()->create();
    $category = Category::factory()->create();
    $book = Book::factory()->for($author)->for($category)->create();
    $library = Library::factory()->create();
    $copy = Copie::factory()->for($book)->for($library)->create();
    $borrow = Borrow::factory()->for($user)->for($copy, 'copy')->create();
    $reserve = Reserve::factory()->for($user)->for($copy, 'copy')->create();
    $favorite = Favorite::factory()->for($user)->for($book)->create();

    $this->assertModelExists($borrow);
    $this->assertModelExists($reserve);
    $this->assertModelExists($favorite);

    expect($copy->book->is($book))->toBeTrue()
        ->and($copy->library->is($library))->toBeTrue()
        ->and($borrow->user->is($user))->toBeTrue()
        ->and($borrow->copy->is($copy))->toBeTrue()
        ->and($reserve->user->is($user))->toBeTrue()
        ->and($reserve->copy->is($copy))->toBeTrue()
        ->and($favorite->user->is($user))->toBeTrue()
        ->and($favorite->book->is($book))->toBeTrue();
});

it('returns all books from their one-to-many category and author relationships', function () {
    $author = Author::factory()->create();
    $category = Category::factory()->create();

    $books = Book::factory()
        ->count(2)
        ->for($author)
        ->for($category)
        ->create();

    expect($category->books->modelKeys())->toBe($books->modelKeys())
        ->and($author->books->modelKeys())->toBe($books->modelKeys());
});

it('manages the many-to-many favorites relationship from either model', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $user->favoriteBooks()->attach($book);

    expect($user->fresh()->favoriteBooks->modelKeys())->toBe([$book->id])
        ->and($book->fresh()->favoritedBy->modelKeys())->toBe([$user->id]);
});

it('stores and updates the deadline on the borrowed-copies pivot', function () {
    $user = User::factory()->create();
    $copy = Copie::factory()->create();

    $user->borrowedCopies()->attach($copy, ['deadline' => '2026-11-01']);

    expect(Carbon::parse($user->fresh()->borrowedCopies->first()->pivot->deadline)->toDateString())->toBe('2026-11-01');

    $user->borrowedCopies()->updateExistingPivot($copy->id, ['deadline' => '2026-11-15']);

    expect(Carbon::parse($user->fresh()->borrowedCopies->first()->pivot->deadline)->toDateString())->toBe('2026-11-15')
        ->and(Carbon::parse(Borrow::where('user_id', $user->id)->where('copy_id', $copy->id)->value('deadline'))->toDateString())->toBe('2026-11-15');
});
