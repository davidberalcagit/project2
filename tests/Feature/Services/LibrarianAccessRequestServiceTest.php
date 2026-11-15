<?php

use App\Models\Library;
use App\Models\User;
use App\Services\LibrarianAccessRequestService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

it('approves a new library proposal and assigns the librarian role', function (): void {
    $user = User::factory()->create();
    $accessRequest = $user->librarianAccessRequest()->create([
        'request_type' => 'new',
        'proposed_library_name' => 'Biblioteca del Barrio',
        'proposed_library_location' => 'Madrid',
    ]);
    $support = User::factory()->create();
    $support->assignRole('Soporte');

    app(LibrarianAccessRequestService::class)->approve($support, $accessRequest);

    $library = Library::query()->where('name', 'Biblioteca del Barrio')->firstOrFail();

    expect($accessRequest->fresh()->status)->toBe('approved')
        ->and($accessRequest->fresh()->requestedLibrary->is($library))->toBeTrue()
        ->and($user->fresh()->library->is($library))->toBeTrue()
        ->and($user->fresh()->hasRole('Bibliotecario'))->toBeTrue();
});

it('rejects a pending request without granting librarian access', function (): void {
    $user = User::factory()->create();
    $accessRequest = $user->librarianAccessRequest()->create();
    $support = User::factory()->create();
    $support->assignRole('Soporte');

    app(LibrarianAccessRequestService::class)->reject($support, $accessRequest);

    expect($accessRequest->fresh()->status)->toBe('rejected')
        ->and($user->fresh()->hasRole('Bibliotecario'))->toBeFalse();
});

it('forbids reviewers without user management permission from approving a request', function (): void {
    $accessRequest = User::factory()->create()
        ->librarianAccessRequest()
        ->create(['request_type' => 'new']);
    $client = User::factory()->create();
    $client->assignRole('Cliente');

    expect(fn () => app(LibrarianAccessRequestService::class)->approve($client, $accessRequest))
        ->toThrow(AuthorizationException::class);

    expect($accessRequest->fresh()->status)->toBe('pending')
        ->and(Library::query()->count())->toBe(0);
});

it('forbids reviewers without user management permission from rejecting a request', function (): void {
    $accessRequest = User::factory()->create()
        ->librarianAccessRequest()
        ->create();
    $client = User::factory()->create();
    $client->assignRole('Cliente');

    expect(fn () => app(LibrarianAccessRequestService::class)->reject($client, $accessRequest))
        ->toThrow(AuthorizationException::class);

    expect($accessRequest->fresh()->status)->toBe('pending');
});

it('does not process a request that has already been reviewed', function (): void {
    $accessRequest = User::factory()->create()
        ->librarianAccessRequest()
        ->create(['status' => 'approved']);
    $support = User::factory()->create();
    $support->assignRole('Soporte');

    expect(fn () => app(LibrarianAccessRequestService::class)->reject($support, $accessRequest))
        ->toThrow(HttpException::class);

    expect($accessRequest->fresh()->status)->toBe('approved');
});
