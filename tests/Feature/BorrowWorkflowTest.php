<?php

use App\Models\Book;
use App\Models\Borrow;
use App\Models\Copie;
use App\Models\Library;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

it('allows a librarian to register a return for a loan in their library', function (): void {
    $this->travelTo('2026-11-02 12:00:00');

    $librarian = User::factory()->create();
    $librarian->assignRole('Bibliotecario');
    $library = Library::factory()->create();
    $librarian->library()->associate($library)->save();
    $borrower = User::factory()->create();
    $copy = Copie::factory()->for($library)->available()->create();
    $borrow = Borrow::factory()->for($borrower)->for($copy, 'copy')->create();

    $this->actingAs($librarian)
        ->post(route('librarian.borrows.complete', $borrow))
        ->assertRedirect(route('librarian.dashboard'))
        ->assertSessionHas('status', 'La devolución del préstamo se ha registrado.');

    expect($borrow->fresh()->returned_at->toDateTimeString())->toBe('2026-11-02 12:00:00')
        ->and($borrow->fresh()->borrowStatus->name)->toBe('Returned')
        ->and($copy->fresh()->copyStatus->name)->toBe('Available');
});

it('does not show another library loans in the librarian dashboard', function (): void {
    $librarian = User::factory()->create();
    $librarian->assignRole('Bibliotecario');
    $library = Library::factory()->create();
    $otherLibrary = Library::factory()->create();
    $librarian->library()->associate($library)->save();
    $borrower = User::factory()->create(['name' => 'Lector propio']);
    $otherBorrower = User::factory()->create(['name' => 'Lector ajeno']);
    $ownCopy = Copie::factory()
        ->for(Book::factory()->create(['title' => 'Libro propio']), 'book')
        ->for($library)
        ->available()
        ->create();
    $otherCopy = Copie::factory()
        ->for(Book::factory()->create(['title' => 'Libro ajeno']), 'book')
        ->for($otherLibrary)
        ->available()
        ->create();
    Borrow::factory()->for($borrower)->for($ownCopy, 'copy')->create();
    Borrow::factory()->for($otherBorrower)->for($otherCopy, 'copy')->create();

    $this->actingAs($librarian)
        ->get(route('librarian.dashboard'))
        ->assertOk()
        ->assertSee('Libro propio')
        ->assertSee('Lector propio')
        ->assertDontSee('Libro ajeno')
        ->assertDontSee('Lector ajeno');
});

it('hides a loan from a librarian assigned to another library', function (): void {
    $librarian = User::factory()->create();
    $librarian->assignRole('Bibliotecario');
    $librarian->library()->associate(Library::factory()->create())->save();
    $otherLibrary = Library::factory()->create();
    $copy = Copie::factory()->for($otherLibrary)->available()->create();
    $borrow = Borrow::factory()->for($copy, 'copy')->create();

    $this->actingAs($librarian)
        ->post(route('librarian.borrows.complete', $borrow))
        ->assertNotFound();

    expect($borrow->fresh()->returned_at)->toBeNull();
});

it('forbids clients from registering loan returns', function (): void {
    $client = User::factory()->create();
    $client->assignRole('Cliente');
    $borrow = Borrow::factory()->create();

    $this->actingAs($client)
        ->post(route('librarian.borrows.complete', $borrow))
        ->assertForbidden();

    expect($borrow->fresh()->returned_at)->toBeNull();
});

it('marks active loans overdue after their deadline', function (): void {
    $this->travelTo('2026-11-01 12:00:00');
    $borrow = Borrow::factory()->create(['deadline' => '2026-11-02']);

    $this->travelTo('2026-11-03 12:00:00');

    $this->artisan('borrows:mark-overdue')
        ->assertSuccessful()
        ->expectsOutput('Updated 1 overdue loan(s).');

    expect($borrow->fresh()->borrowStatus->name)->toBe('Overdue')
        ->and($borrow->fresh()->copy->copyStatus->name)->toBe('Borrowed');

    $this->artisan('borrows:mark-overdue')
        ->assertSuccessful()
        ->expectsOutput('Updated 0 overdue loan(s).');
});
