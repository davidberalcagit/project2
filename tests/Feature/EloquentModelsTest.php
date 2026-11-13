<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\Borrow;
use App\Models\Category;
use App\Models\Copie;
use App\Models\Favorite;
use App\Models\Library;
use App\Models\Opinion;
use App\Models\Reserve;
use App\Models\User;
use Database\Seeders\AuthorSeeder;
use Database\Seeders\BookSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\FavoriteSeeder;
use Database\Seeders\OpinionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

it('connects books, copies, libraries, borrows, reserves, and users through their relationships', function () {
    $book = Book::create([
        'title' => 'Proyecto Hail Mary',
        'editorial' => 'Editorial Nova',
        'publication_date' => '2021-01-01',
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
    $reserveCopy = Copie::factory()
        ->for($book)
        ->for($library)
        ->available()
        ->create();
    $user = User::create([
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.test',
        'password' => 'password',
    ]);
    $borrow = Borrow::create([
        'copy_id' => $copy->id,
        'user_id' => $user->id,
        'deadline' => '2026-11-28',
    ]);
    $reserve = Reserve::create([
        'copy_id' => $reserveCopy->id,
        'user_id' => $user->id,
    ]);

    expect($book->copies->modelKeys())->toBe([$copy->id, $reserveCopy->id])
        ->and($library->copies->modelKeys())->toBe([$copy->id, $reserveCopy->id])
        ->and($copy->book->is($book))->toBeTrue()
        ->and($copy->library->is($library))->toBeTrue()
        ->and($copy->borrows->modelKeys())->toBe([$borrow->id])
        ->and($reserveCopy->reserves->modelKeys())->toBe([$reserve->id])
        ->and($borrow->copy->is($copy))->toBeTrue()
        ->and($borrow->user->is($user))->toBeTrue()
        ->and($reserve->copy->is($reserveCopy))->toBeTrue()
        ->and($reserve->user->is($user))->toBeTrue()
        ->and($user->borrows->modelKeys())->toBe([$borrow->id])
        ->and($user->reserves->modelKeys())->toBe([$reserve->id])
        ->and($user->borrows->first()->loaned_at)->not->toBeNull()
        ->and($user->borrows->first()->returned_at)->toBeNull();
});

it('casts a book publication date and a borrow deadline to dates', function () {
    $book = new Book(['publication_date' => '2021-01-01']);
    $borrow = new Borrow(['deadline' => '2026-10-28']);

    expect($book->publication_date->toDateString())->toBe('2021-01-01')
        ->and($borrow->deadline->toDateString())->toBe('2026-10-28');
});

it('creates books with category and author relationships using factories', function () {
    $book = Book::factory()->create();

    expect($book->category)->toBeInstanceOf(Category::class)
        ->and($book->author)->toBeInstanceOf(Author::class)
        ->and($book->category->books->modelKeys())->toBe([$book->id])
        ->and($book->author->books->modelKeys())->toBe([$book->id]);
});

it('rejects books with duplicate ISBNs', function () {
    $book = Book::factory()->create(['isbn' => '9780000000001']);

    expect(fn () => Book::factory()->create(['isbn' => $book->isbn]))
        ->toThrow(QueryException::class);
});

it('rejects duplicate favorites for the same user and book', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();
    Favorite::factory()->for($user)->for($book)->create();

    expect(fn () => Favorite::factory()->for($user)->for($book)->create())
        ->toThrow(QueryException::class);
});

it('creates categories with unique names through the factory', function () {
    Category::factory(10)->create();

    expect(Category::query()->distinct('name')->count('name'))->toBe(10);
});

it('seeds categories idempotently', function () {
    $this->seed(CategorySeeder::class);
    $this->seed(CategorySeeder::class);

    expect(Category::query()->count())->toBe(8)
        ->and(Category::query()->distinct('name')->count('name'))->toBe(8);
});

it('associates opinions with their book and user', function () {
    $opinion = Opinion::factory()->create([
        'rating' => 4,
        'comment' => 'Muy recomendable.',
    ]);

    expect($opinion->rating)->toBe(4)
        ->and($opinion->comment)->toBe('Muy recomendable.')
        ->and($opinion->user->opinions->modelKeys())->toBe([$opinion->id])
        ->and($opinion->book->opinions->modelKeys())->toBe([$opinion->id]);
});

it('rejects opinion ratings outside the range of one to five', function (int $rating) {
    expect(fn () => Opinion::factory()->create(['rating' => $rating]))
        ->toThrow(ValidationException::class);

    $this->assertDatabaseCount('opinions', 0);
})->with([0, 6]);

it('seeds two opinions for every book idempotently', function () {
    Book::factory(7)->create();
    User::factory(2)->create();

    $this->seed(OpinionSeeder::class);
    $initialOpinionCount = Opinion::count();
    $this->seed(OpinionSeeder::class);

    expect(Book::query()->withCount('opinions')->get()->pluck('opinions_count')->unique()->all())->toBe([2])
        ->and(Opinion::count())->toBe($initialOpinionCount);
});

it('preserves existing opinions while filling each book to two', function () {
    $books = Book::factory(2)->create();
    $reviewer = User::factory()->create();
    Opinion::factory()->for($books->first())->for($reviewer)->create([
        'rating' => 3,
        'comment' => 'Valoración existente.',
    ]);

    $this->seed(OpinionSeeder::class);

    expect($books->first()->opinions()->count())->toBe(2)
        ->and($books->first()->opinions()->where('comment', 'Valoración existente.')->value('rating'))->toBe(3)
        ->and($books->last()->opinions()->count())->toBe(2);
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
        ->and(Book::whereNotNull('category_id')->count())->toBe(10)
        ->and(Book::whereNotNull('author_id')->count())->toBe(10)
        ->and(Favorite::count())->toBe(User::count() * Book::count())
        ->and(User::first()->favoriteBooks->count())->toBe(Book::count());
});
