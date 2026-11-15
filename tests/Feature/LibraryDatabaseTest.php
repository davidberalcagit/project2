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
use Database\Seeders\BorrowSeeder;
use Database\Seeders\BorrowStatusSeeder;
use Database\Seeders\CopieSeeder;
use Database\Seeders\CopyStatusSeeder;
use Database\Seeders\LibrarySeeder;
use Database\Seeders\ReserveSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

it('creates every library model with its factory and persists its required relationships', function () {
    $user = User::factory()->create();
    $author = Author::factory()->create();
    $category = Category::factory()->create();
    $book = Book::factory()->for($author)->for($category)->create();
    $library = Library::factory()->create();
    $copy = Copie::factory()->for($book)->for($library)->create();
    $reserveCopy = Copie::factory()->for($book)->for($library)->available()->create();
    $borrow = Borrow::factory()->for($user)->for($copy, 'copy')->create();
    $reserve = Reserve::factory()->for($user)->for($reserveCopy, 'copy')->create();
    $favorite = Favorite::factory()->for($user)->for($book)->create();

    $this->assertModelExists($borrow);
    $this->assertModelExists($reserve);
    $this->assertModelExists($favorite);

    expect($copy->book->is($book))->toBeTrue()
        ->and($copy->library->is($library))->toBeTrue()
        ->and($borrow->user->is($user))->toBeTrue()
        ->and($borrow->copy->is($copy))->toBeTrue()
        ->and($reserve->user->is($user))->toBeTrue()
        ->and($reserve->copy->is($reserveCopy))->toBeTrue()
        ->and($favorite->user->is($user))->toBeTrue()
        ->and($favorite->book->is($book))->toBeTrue();
});

it('sets a reservation start date and expiration date 24 hours apart', function () {
    $this->freezeTime();

    $reserve = Reserve::factory()->create();
    $reserve->refresh();

    expect($reserve->start_date->toDateTimeString())->toBe(now()->toDateTimeString())
        ->and($reserve->expiration_date->toDateTimeString())->toBe(now()->addHours(24)->toDateTimeString());
});

it('marks a copy reserved when a reservation starts and available when it expires', function () {
    $this->travelTo(new \DateTimeImmutable('2026-11-06 11:59:00'));

    $copy = Copie::factory()->available()->create();
    Reserve::factory()
        ->for($copy, 'copy')
        ->create([
            'start_date' => '2026-11-06 12:00:00',
            'expiration_date' => '2026-11-07 12:00:00',
        ]);

    expect($copy->fresh()->copyStatus->name)->toBe('Available');

    $this->travelTo(new \DateTimeImmutable('2026-11-06 12:00:00'));
    $this->artisan('copies:sync-availability')->assertSuccessful();

    expect($copy->fresh()->copyStatus->name)->toBe('Reserved');

    $this->travelTo(new \DateTimeImmutable('2026-11-07 12:00:00'));
    $this->artisan('copies:sync-availability')->assertSuccessful();

    expect($copy->fresh()->copyStatus->name)->toBe('Available');
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

it('keeps multiple loans in the borrow history and renews the selected loan', function () {
    $this->travelTo(new \DateTimeImmutable('2026-10-28 12:00:00'));

    $user = User::factory()->create();
    $copy = Copie::factory()->available()->create();
    $firstBorrow = Borrow::factory()
        ->for($user)
        ->for($copy, 'copy')
        ->create(['deadline' => '2026-11-01']);

    $firstBorrow->markAsReturned();

    $secondBorrow = Borrow::factory()
        ->for($user)
        ->for($copy, 'copy')
        ->create(['deadline' => '2026-11-07']);
    $secondBorrow->renewUntil(new DateTimeImmutable('2026-11-15'));

    expect($user->fresh()->borrows->modelKeys())->toBe([$firstBorrow->id, $secondBorrow->id])
        ->and($copy->fresh()->borrows->modelKeys())->toBe([$firstBorrow->id, $secondBorrow->id])
        ->and($firstBorrow->fresh()->deadline->toDateString())->toBe('2026-11-01')
        ->and($firstBorrow->fresh()->returned_at)->not->toBeNull()
        ->and($secondBorrow->fresh()->deadline->toDateString())->toBe('2026-11-15')
        ->and($secondBorrow->fresh()->returned_at)->toBeNull();
});

it('rejects a loan deadline that is today or in the past', function (string $deadline) {
    $this->travelTo(new \DateTimeImmutable('2026-11-01 12:00:00'));

    expect(fn () => Borrow::factory()->create(['deadline' => $deadline]))
        ->toThrow(ValidationException::class);

    $this->assertDatabaseCount('borrows', 0);
})->with(['2026-11-01', '2026-10-31']);

it('allows an overdue loan to be returned', function () {
    $this->travelTo(new \DateTimeImmutable('2026-10-01 12:00:00'));
    $borrow = Borrow::factory()->create(['deadline' => '2026-10-02']);

    $this->travelTo(new \DateTimeImmutable('2026-10-03 12:00:00'));
    $borrow->markAsReturned();

    expect($borrow->fresh()->returned_at->toDateTimeString())->toBe('2026-10-03 12:00:00');
});

it('rejects a second active loan for a copy', function () {
    $copy = Copie::factory()->available()->create();
    Borrow::factory()->for($copy, 'copy')->create();

    expect(fn () => Borrow::factory()->for($copy, 'copy')->create())
        ->toThrow(ValidationException::class);

    $this->assertDatabaseCount('borrows', 1);
});

it('preserves loan history when deleting its user or copy', function () {
    $borrow = Borrow::factory()->create();
    $borrow->markAsReturned();

    expect(fn () => $borrow->user->delete())
        ->toThrow(QueryException::class);

    expect(fn () => $borrow->copy->delete())
        ->toThrow(QueryException::class);

    $this->assertDatabaseCount('borrows', 1);
});

it('preserves reservation history when deleting its user or copy', function () {
    $reserve = Reserve::factory()->create();

    expect(fn () => $reserve->user->delete())
        ->toThrow(QueryException::class);

    expect(fn () => $reserve->copy->delete())
        ->toThrow(QueryException::class);

    $this->assertDatabaseCount('reserves', 1);
});

it('seeds each copy in only one coherent availability state', function () {
    Book::factory(6)->create();
    Library::factory()->create();
    User::factory()->create();

    $this->seed([
        CopyStatusSeeder::class,
        BorrowStatusSeeder::class,
        CopieSeeder::class,
        BorrowSeeder::class,
        ReserveSeeder::class,
    ]);

    $copies = Copie::query()->with('copyStatus')->get();

    foreach ($copies as $copy) {
        $hasActiveBorrow = $copy->borrows()->whereNull('returned_at')->exists();
        $hasActiveReservation = $copy->reserves()
            ->where('start_date', '<=', now())
            ->where('expiration_date', '>', now())
            ->exists();

        expect([$copy->copyStatus->name, $hasActiveBorrow, $hasActiveReservation])->toBeIn([
            ['Borrowed', true, false],
            ['Reserved', false, true],
            ['Available', false, false],
        ]);
    }

    $this->seed([BorrowSeeder::class, ReserveSeeder::class]);

    $this->assertDatabaseCount('borrows', 6);
    $this->assertDatabaseCount('reserves', 4);
});

it('distributes seeded copies across every library and can run repeatedly', function () {
    Book::factory(3)->create();
    $this->seed(LibrarySeeder::class);
    $this->seed(LibrarySeeder::class);
    Library::factory(7)->create();

    $this->seed(CopyStatusSeeder::class);
    $this->seed(CopieSeeder::class);

    expect(Library::query()->withCount('copies')->orderBy('id')->get()->pluck('copies_count')->all())->toBe(array_fill(0, 10, 1));
    expect(Book::query()->withCount('copies')->orderBy('id')->get()->pluck('copies_count')->all())->toBe([4, 3, 3]);

    $initialCopiesCount = Copie::query()->count();
    $this->seed(CopieSeeder::class);

    expect(Copie::query()->count())->toBe($initialCopiesCount)
        ->and(Library::query()->withCount('copies')->orderBy('id')->get()->pluck('copies_count')->all())->toBe(array_fill(0, 10, 1))
        ->and(Book::query()->withCount('copies')->orderBy('id')->get()->pluck('copies_count')->all())->toBe([4, 3, 3]);
});
