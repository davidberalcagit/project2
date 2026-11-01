<?php

use App\Models\Book;
use App\Models\Copie;
use App\Models\CopyStatus;
use App\Models\Library;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

it('allows librarians to create a copy in their assigned library', function (): void {
    $librarian = User::factory()->create();
    $librarian->assignRole('Bibliotecario');
    $book = Book::factory()->create(['title' => 'El nombre del viento']);
    $library = Library::factory()->create(['name' => 'Biblioteca Central']);
    $librarian->library()->associate($library)->save();
    $status = CopyStatus::factory()->create(['name' => 'Available']);

    $this->actingAs($librarian)
        ->get(route('librarian.copies.create'))
        ->assertOk()
        ->assertSee('El nombre del viento')
        ->assertDontSee('name="library_id"');

    $this->actingAs($librarian)
        ->post(route('librarian.copies.store'), [
            'book_id' => $book->id,
            'barcode' => 'LIB-00000001',
            'copy_status_id' => $status->id,
            'library_id' => Library::factory()->create()->id,
        ])
        ->assertRedirect(route('librarian.dashboard'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('copies', [
        'book_id' => $book->id,
        'library_id' => $library->id,
        'barcode' => 'LIB-00000001',
        'copy_status_id' => $status->id,
    ]);
});

it('rejects duplicate copy barcodes without creating another copy', function (): void {
    $librarian = User::factory()->create();
    $librarian->assignRole('Bibliotecario');
    $librarian->library()->associate(Library::factory()->create())->save();
    $copy = Copie::factory()->create(['barcode' => 'LIB-00000001']);
    $book = Book::factory()->create();
    $status = CopyStatus::factory()->create();

    $this->actingAs($librarian)
        ->from(route('librarian.copies.create'))
        ->post(route('librarian.copies.store'), [
            'book_id' => $book->id,
            'barcode' => $copy->barcode,
            'copy_status_id' => $status->id,
        ])
        ->assertRedirect(route('librarian.copies.create'))
        ->assertSessionHasErrors('barcode');

    $this->assertDatabaseCount('copies', 1);
});

it('shows only copies belonging to the signed-in librarian library', function (): void {
    $librarian = User::factory()->create();
    $librarian->assignRole('Bibliotecario');
    $library = Library::factory()->create(['name' => 'Biblioteca propia']);
    $otherLibrary = Library::factory()->create(['name' => 'Otra biblioteca']);
    $librarian->library()->associate($library)->save();

    $ownCopy = Copie::factory()
        ->for(Book::factory()->create(['title' => 'Libro propio']))
        ->for($library)
        ->create(['barcode' => 'OWN-00000001']);
    Copie::factory()
        ->for(Book::factory()->create(['title' => 'Libro ajeno']))
        ->for($otherLibrary)
        ->create(['barcode' => 'OTHER-0000001']);

    $this->actingAs($librarian)
        ->get(route('librarian.dashboard'))
        ->assertOk()
        ->assertSee('Biblioteca propia')
        ->assertSee('Libro propio')
        ->assertSee('OWN-00000001')
        ->assertDontSee('Libro ajeno')
        ->assertDontSee('OTHER-0000001');

    expect($librarian->library->copies->modelKeys())->toBe([$ownCopy->id]);
});

it('prevents clients from accessing or submitting the copy form', function (): void {
    $client = User::factory()->create();
    $client->assignRole('Cliente');

    $this->actingAs($client)
        ->get(route('librarian.copies.create'))
        ->assertForbidden();

    $this->actingAs($client)
        ->post(route('librarian.copies.store'), [])
        ->assertForbidden();
});

it('prevents librarians without an assigned library from creating copies', function (): void {
    $librarian = User::factory()->create();
    $librarian->assignRole('Bibliotecario');

    $this->actingAs($librarian)
        ->get(route('librarian.copies.create'))
        ->assertForbidden();

    $this->actingAs($librarian)
        ->post(route('librarian.copies.store'), [
            'book_id' => Book::factory()->create()->id,
            'barcode' => 'LIB-00000001',
            'copy_status_id' => CopyStatus::factory()->create()->id,
        ])
        ->assertForbidden();

    $this->assertDatabaseCount('copies', 0);
});
