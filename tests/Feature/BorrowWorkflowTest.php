<?php

use App\Models\Book;
use App\Models\Borrow;
use App\Models\Copie;
use App\Models\Library;
use App\Models\Reserve;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

it('allows a librarian to register a return for a loan in their library', function (): void {
    $this->travelTo(new \DateTimeImmutable('2026-11-02 12:00:00'));

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

it('logs a reservation-holder notification when a reserved loan is returned', function (): void {
    $this->travelTo(new \DateTimeImmutable('2026-11-02 12:00:00'));

    $librarian = User::factory()->create();
    $librarian->assignRole('Bibliotecario');
    $library = Library::factory()->create();
    $librarian->library()->associate($library)->save();
    $copy = Copie::factory()->for($library)->available()->create();
    $reserve = Reserve::factory()
        ->for($copy, 'copy')
        ->create([
            'start_date' => now()->subHour(),
            'expiration_date' => now()->addHour(),
        ]);
    $borrow = Borrow::factory()
        ->for(User::factory())
        ->for($copy, 'copy')
        ->create();

    Log::spy();

    $this->actingAs($librarian)
        ->post(route('librarian.borrows.complete', $borrow))
        ->assertRedirect(route('librarian.dashboard'));

    expect($borrow->fresh()->returned_at)->not->toBeNull()
        ->and($copy->fresh()->copyStatus->name)->toBe('Reserved');

    Log::shouldHaveReceived('info')->once()->with(
        'Future reservation-holder email notification prepared.',
        [
            'copy_id' => $copy->id,
            'reservation_id' => $reserve->id,
            'user_id' => $reserve->user_id,
        ],
    );
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

it('shows only overdue loans when the librarian selects the overdue filter', function (): void {
    $this->travelTo(new \DateTimeImmutable('2026-11-10 12:00:00'));

    $librarian = User::factory()->create();
    $librarian->assignRole('Bibliotecario');
    $library = Library::factory()->create();
    $librarian->library()->associate($library)->save();
    $overdueCopy = Copie::factory()
        ->for(Book::factory()->create(['title' => 'Préstamo vencido']), 'book')
        ->for($library)
        ->available()
        ->create();
    $activeCopy = Copie::factory()
        ->for(Book::factory()->create(['title' => 'Préstamo vigente']), 'book')
        ->for($library)
        ->available()
        ->create();
    $overdueBorrow = Borrow::factory()->for($overdueCopy, 'copy')->create(['deadline' => '2026-11-11']);
    Borrow::factory()->for($activeCopy, 'copy')->create(['deadline' => '2026-11-15']);
    DB::table('borrows')->where('id', $overdueBorrow->id)->update(['deadline' => '2026-11-09']);

    $response = $this->actingAs($librarian)
        ->get(route('librarian.dashboard', ['loan_filter' => 'overdue']))
        ->assertOk()
        ->assertSee('Solo vencidos');

    $response->assertViewHas('activeBorrows', fn ($borrows) => $borrows->modelKeys() === [$overdueBorrow->id]);
});

it('shows only the signed-in user recent loans on the personal dashboard', function (): void {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();
    $ownCopy = Copie::factory()
        ->for(Book::factory()->create(['title' => 'Mi préstamo']), 'book')
        ->create();
    $otherCopy = Copie::factory()
        ->for(Book::factory()->create(['title' => 'Préstamo ajeno']), 'book')
        ->create();
    Borrow::factory()->for($user)->for($ownCopy, 'copy')->create();
    Borrow::factory()->for($anotherUser)->for($otherCopy, 'copy')->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Mi préstamo')
        ->assertDontSee('Préstamo ajeno');
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
    $this->travelTo(new \DateTimeImmutable('2026-11-01 12:00:00'));
    $borrow = Borrow::factory()->create(['deadline' => '2026-11-02']);

    $this->travelTo(new \DateTimeImmutable('2026-11-03 12:00:00'));
    Log::spy();

    $this->artisan('borrows:mark-overdue')
        ->assertSuccessful()
        ->expectsOutput('Updated 1 overdue loan(s).');

    expect($borrow->fresh()->borrowStatus->name)->toBe('Overdue')
        ->and($borrow->fresh()->copy->copyStatus->name)->toBe('Borrowed');

    $this->artisan('borrows:mark-overdue')
        ->assertSuccessful()
        ->expectsOutput('Updated 0 overdue loan(s).');

    Log::shouldHaveReceived('info')->once()->with(
        'Future overdue loan email notification prepared.',
        ['borrow_id' => $borrow->id, 'user_id' => $borrow->user_id],
    );
});
