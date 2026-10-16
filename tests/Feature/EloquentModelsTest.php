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
use Database\Seeders\AuthorSeeder;
use Database\Seeders\BookSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\FavoriteSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

it('connects books, copies, libraries, borrows, reserves, and users through their relationships', function () {
    $book = Book::create([
        'title' => 'Proyecto Hail Mary',
        'editorial' => 'Editorial Nova',
        'year' => 2021,
        'edition' => '1',
        'isbn' => '978841803718',
    ]);
    $library = Library::create([
        'name' => 'Biblioteca Central',
        'location' => 'Madrid',
    ]);
    $copy = Copie::create([
        'book_id' => $book->id,
        'library_id' => $library->id,
        'barcode' => 'COPY-001',
    ]);
    $user = User::create([
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.test',
        'password' => 'password',
    ]);
    $borrow = Borrow::create([
        'copy_id' => $copy->id,
        'user_id' => $user->id,
        'deadline' => '2026-10-28',
    ]);
    $reserve = Reserve::create([
        'copy_id' => $copy->id,
        'user_id' => $user->id,
    ]);

    expect($book->copies->modelKeys())->toBe([$copy->id])
        ->and($library->copies->modelKeys())->toBe([$copy->id])
        ->and($copy->book->is($book))->toBeTrue()
        ->and($copy->library->is($library))->toBeTrue()
        ->and($copy->borrows->modelKeys())->toBe([$borrow->id])
        ->and($copy->reserves->modelKeys())->toBe([$reserve->id])
        ->and($borrow->copy->is($copy))->toBeTrue()
        ->and($borrow->user->is($user))->toBeTrue()
        ->and($reserve->copy->is($copy))->toBeTrue()
        ->and($reserve->user->is($user))->toBeTrue()
        ->and($user->borrows->modelKeys())->toBe([$borrow->id])
        ->and($user->reserves->modelKeys())->toBe([$reserve->id])
        ->and($user->borrowedCopies->modelKeys())->toBe([$copy->id])
        ->and($user->borrowedCopies->first()->pivot->getRawOriginal('deadline'))->toBe('2026-10-28 00:00:00')
        ->and($copy->borrowers->modelKeys())->toBe([$user->id]);
});

it('casts a book year to an integer and a borrow deadline to a date', function () {
    $book = new Book(['year' => '2021']);
    $borrow = new Borrow(['deadline' => '2026-10-28']);

    expect($book->year)->toBe(2021)
        ->and($borrow->deadline->toDateString())->toBe('2026-10-28');
});

it('creates books with category and author relationships using factories', function () {
    $book = Book::factory()->create();

    expect($book->category)->toBeInstanceOf(Category::class)
        ->and($book->author)->toBeInstanceOf(Author::class)
        ->and($book->category->books->modelKeys())->toBe([$book->id])
        ->and($book->author->books->modelKeys())->toBe([$book->id]);
});

it('seeds categories, authors, books, and favorites', function () {
    User::factory()->create([
        'name' => 'admin',
        'email' => 'admin@gmail.com',
    ]);

    $this->seed(CategorySeeder::class);
    $this->seed(AuthorSeeder::class);
    $this->seed(BookSeeder::class);
    User::factory()->create();
    $this->seed(FavoriteSeeder::class);
    $this->seed(FavoriteSeeder::class);

    expect(Category::count())->toBe(8)
        ->and(Author::count())->toBe(8)
        ->and(Book::whereNotNull('category_id')->count())->toBe(6)
        ->and(Book::whereNotNull('author_id')->count())->toBe(6)
        ->and(Favorite::count())->toBe(User::count() * Book::count())
        ->and(User::first()->favoriteBooks->count())->toBe(Book::count());
});
