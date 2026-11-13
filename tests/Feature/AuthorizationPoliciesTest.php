<?php

use App\Models\Book;
use App\Models\Borrow;
use App\Models\Copie;
use App\Models\LibrarianAccessRequest;
use App\Models\Library;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

it('allows content managers and support to manage books but denies clients', function (): void {
    $book = Book::factory()->create();
    $contentManager = User::factory()->create();
    $contentManager->assignRole('Gestor de contenidos');
    $support = User::factory()->create();
    $support->assignRole('Soporte');
    $client = User::factory()->create();
    $client->assignRole('Cliente');

    expect($contentManager->can('create', Book::class))->toBeTrue()
        ->and($contentManager->can('update', $book))->toBeTrue()
        ->and($contentManager->can('delete', $book))->toBeTrue()
        ->and($support->can('create', Book::class))->toBeTrue()
        ->and($support->can('update', $book))->toBeTrue()
        ->and($support->can('delete', $book))->toBeTrue()
        ->and($client->can('create', Book::class))->toBeFalse()
        ->and($client->can('update', $book))->toBeFalse()
        ->and($client->can('delete', $book))->toBeFalse();
});

it('allows content managers and support to manage libraries but denies clients', function (): void {
    $library = Library::factory()->create();
    $contentManager = User::factory()->create();
    $contentManager->assignRole('Gestor de contenidos');
    $support = User::factory()->create();
    $support->assignRole('Soporte');
    $client = User::factory()->create();
    $client->assignRole('Cliente');

    expect($contentManager->can('create', Library::class))->toBeTrue()
        ->and($contentManager->can('update', $library))->toBeTrue()
        ->and($contentManager->can('delete', $library))->toBeTrue()
        ->and($support->can('create', Library::class))->toBeTrue()
        ->and($support->can('update', $library))->toBeTrue()
        ->and($support->can('delete', $library))->toBeTrue()
        ->and($client->can('create', Library::class))->toBeFalse()
        ->and($client->can('update', $library))->toBeFalse()
        ->and($client->can('delete', $library))->toBeFalse();
});

it('allows copy management only for users with permission and an assigned library', function (): void {
    $library = Library::factory()->create();
    $librarian = User::factory()->create();
    $librarian->assignRole('Bibliotecario');
    $librarian->library()->associate($library)->save();
    $unassignedLibrarian = User::factory()->create();
    $unassignedLibrarian->assignRole('Bibliotecario');
    $client = User::factory()->create();
    $client->assignRole('Cliente');

    expect($librarian->can('create', Copie::class))->toBeTrue()
        ->and($unassignedLibrarian->can('create', Copie::class))->toBeFalse()
        ->and($client->can('create', Copie::class))->toBeFalse();
});

it('allows loan returns for authorized librarians and global managers only', function (): void {
    $library = Library::factory()->create();
    $otherLibrary = Library::factory()->create();
    $copy = Copie::factory()->for($library)->create();
    $borrow = Borrow::factory()->for($copy, 'copy')->create();
    $librarian = User::factory()->create();
    $librarian->assignRole('Bibliotecario');
    $librarian->library()->associate($library)->save();
    $otherLibrarian = User::factory()->create();
    $otherLibrarian->assignRole('Bibliotecario');
    $otherLibrarian->library()->associate($otherLibrary)->save();
    $internal = User::factory()->create();
    $internal->assignRole('Internos');
    $client = User::factory()->create();
    $client->assignRole('Cliente');

    expect($librarian->can('complete', $borrow))->toBeTrue()
        ->and($otherLibrarian->can('complete', $borrow))->toBeFalse()
        ->and($internal->can('complete', $borrow))->toBeTrue()
        ->and($client->can('complete', $borrow))->toBeFalse();
});

it('allows support to review librarian access requests but denies clients', function (): void {
    $accessRequest = User::factory()->create()
        ->librarianAccessRequest()
        ->create();
    $support = User::factory()->create();
    $support->assignRole('Soporte');
    $client = User::factory()->create();
    $client->assignRole('Cliente');

    expect($support->can('viewAny', LibrarianAccessRequest::class))->toBeTrue()
        ->and($support->can('approve', $accessRequest))->toBeTrue()
        ->and($support->can('reject', $accessRequest))->toBeTrue()
        ->and($client->can('viewAny', LibrarianAccessRequest::class))->toBeFalse()
        ->and($client->can('approve', $accessRequest))->toBeFalse()
        ->and($client->can('reject', $accessRequest))->toBeFalse();
});
