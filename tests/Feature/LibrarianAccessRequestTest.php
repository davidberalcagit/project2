<?php

use App\Models\Library;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

it('allows an authenticated client to request access to an existing library', function (): void {
    $user = User::factory()->create();
    $user->assignRole('Cliente');
    $library = Library::factory()->create(['name' => 'Biblioteca Central']);

    $this->actingAs($user)
        ->get(route('librarian-access-requests.create'))
        ->assertOk()
        ->assertSee('Solicitar acceso como bibliotecario')
        ->assertSee('Biblioteca Central');

    $this->actingAs($user)
        ->post(route('librarian-access-requests.store'), [
            'request_type' => 'existing',
            'library_id' => $library->id,
        ])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('status');

    $accessRequest = $user->librarianAccessRequest()->firstOrFail();

    $this->assertDatabaseHas('librarian_access_requests', [
        'user_id' => $user->id,
        'status' => 'pending',
        'request_type' => 'existing',
        'requested_library_id' => $library->id,
    ]);

    expect($user->fresh()->hasRole('Cliente'))->toBeTrue()
        ->and($user->fresh()->hasRole('Bibliotecario'))->toBeFalse()
        ->and($user->fresh()->can('access-library'))->toBeFalse();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('pendiente de aprobación')
        ->assertDontSee('Solicitar acceso como bibliotecario');

    $this->actingAs($user)
        ->get(route('librarian-access-requests.create'))
        ->assertRedirect(route('dashboard'));

    expect($accessRequest->status)->toBe('pending');
});

it('registers new users as clients without creating access requests', function (): void {
    $this->get(route('auth.register'))
        ->assertOk()
        ->assertDontSee('¿Eres una biblioteca?');

    $this->post(route('auth.register.store'), [
        'name' => 'Biblioteca Central',
        'email' => 'biblioteca@example.test',
        'password' => 'reading-books-123',
        'password_confirmation' => 'reading-books-123',
    ])->assertRedirect(route('dashboard'));

    $user = User::where('email', 'biblioteca@example.test')->firstOrFail();

    $this->assertDatabaseCount('librarian_access_requests', 0);

    expect($user->hasRole('Cliente'))->toBeTrue()
        ->and($user->hasRole('Bibliotecario'))->toBeFalse()
        ->and($user->can('access-library'))->toBeFalse();

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Solicitar acceso como bibliotecario');
});

it('stores a new library proposal without creating a library before approval', function (): void {
    $user = User::factory()->create();
    $user->assignRole('Cliente');

    $this->actingAs($user)
        ->post(route('librarian-access-requests.store'), [
            'request_type' => 'new',
            'proposed_library_name' => 'Biblioteca del Barrio',
            'proposed_library_location' => 'Madrid',
        ])
        ->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('librarian_access_requests', [
        'user_id' => $user->id,
        'status' => 'pending',
        'request_type' => 'new',
        'requested_library_id' => null,
        'proposed_library_name' => 'Biblioteca del Barrio',
        'proposed_library_location' => 'Madrid',
    ]);
    $this->assertDatabaseCount('libraries', 0);
    expect($user->fresh()->hasRole('Bibliotecario'))->toBeFalse();
});

it('validates the selected existing library and proposed library details', function (): void {
    $user = User::factory()->create();
    $user->assignRole('Cliente');

    $this->actingAs($user)
        ->from(route('librarian-access-requests.create'))
        ->post(route('librarian-access-requests.store'), [
            'request_type' => 'existing',
            'library_id' => 999999,
        ])
        ->assertRedirect(route('librarian-access-requests.create'))
        ->assertSessionHasErrors('library_id');

    $this->actingAs($user)
        ->from(route('librarian-access-requests.create'))
        ->post(route('librarian-access-requests.store'), [
            'request_type' => 'new',
            'proposed_library_name' => '',
            'proposed_library_location' => '',
        ])
        ->assertRedirect(route('librarian-access-requests.create'))
        ->assertSessionHasErrors(['proposed_library_name', 'proposed_library_location']);

    $this->assertDatabaseCount('librarian_access_requests', 0);
    $this->assertDatabaseCount('libraries', 0);
});

it('allows support to approve access to an existing library', function (): void {
    $user = User::factory()->create();
    $library = Library::factory()->create();
    $accessRequest = $user->librarianAccessRequest()->create([
        'request_type' => 'existing',
        'requested_library_id' => $library->id,
    ]);
    $support = User::factory()->create();
    $support->assignRole('Soporte');

    $this->actingAs($support)
        ->get(route('support.dashboard'))
        ->assertOk()
        ->assertSee('Biblioteca')
        ->assertSee($library->name);

    $this->actingAs($support)
        ->post(route('support.librarian-requests.approve', $accessRequest))
        ->assertRedirect(route('support.dashboard'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('librarian_access_requests', [
        'id' => $accessRequest->id,
        'status' => 'approved',
        'reviewed_by' => $support->id,
    ]);

    expect($user->fresh()->hasRole('Bibliotecario'))->toBeTrue()
        ->and($user->fresh()->can('access-library'))->toBeTrue()
        ->and($user->fresh()->library->is($library))->toBeTrue();
});

it('creates and assigns a proposed library when support approves the request', function (): void {
    $user = User::factory()->create();
    $accessRequest = $user->librarianAccessRequest()->create([
        'request_type' => 'new',
        'proposed_library_name' => 'Biblioteca del Barrio',
        'proposed_library_location' => 'Madrid',
    ]);
    $support = User::factory()->create();
    $support->assignRole('Soporte');

    $this->actingAs($support)
        ->get(route('support.dashboard'))
        ->assertOk()
        ->assertSee('Biblioteca del Barrio')
        ->assertSee('Madrid');

    $this->actingAs($support)
        ->post(route('support.librarian-requests.approve', $accessRequest))
        ->assertRedirect(route('support.dashboard'));

    $library = Library::query()->where('name', 'Biblioteca del Barrio')->firstOrFail();

    $this->assertDatabaseHas('librarian_access_requests', [
        'id' => $accessRequest->id,
        'status' => 'approved',
        'requested_library_id' => $library->id,
        'reviewed_by' => $support->id,
    ]);
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'library_id' => $library->id,
    ]);

    expect($user->fresh()->hasRole('Bibliotecario'))->toBeTrue()
        ->and(Library::query()->where('name', 'Biblioteca del Barrio')->count())->toBe(1);
});

it('requires library management permission to approve a new library proposal', function (): void {
    $user = User::factory()->create();
    $accessRequest = $user->librarianAccessRequest()->create([
        'request_type' => 'new',
        'proposed_library_name' => 'Biblioteca restringida',
        'proposed_library_location' => 'Madrid',
    ]);
    $reviewerRole = Role::query()->create([
        'name' => 'Access request reviewer',
        'guard_name' => 'web',
    ]);
    $reviewerRole->givePermissionTo(Permission::findByName('manage-users', 'web'));
    $reviewer = User::factory()->create();
    $reviewer->assignRole($reviewerRole);

    $this->actingAs($reviewer)
        ->post(route('support.librarian-requests.approve', $accessRequest))
        ->assertForbidden();

    $this->assertDatabaseCount('libraries', 0);
    $this->assertDatabaseHas('librarian_access_requests', [
        'id' => $accessRequest->id,
        'status' => 'pending',
    ]);
    expect($user->fresh()->hasRole('Bibliotecario'))->toBeFalse();
});

it('requires support to select an existing library before approving a request', function (): void {
    $user = User::factory()->create();
    $accessRequest = $user->librarianAccessRequest()->create();
    $support = User::factory()->create();
    $support->assignRole('Soporte');

    $this->actingAs($support)
        ->from(route('support.dashboard'))
        ->post(route('support.librarian-requests.approve', $accessRequest), [])
        ->assertRedirect(route('support.dashboard'))
        ->assertSessionHasErrors('library_id');

    expect($user->fresh()->hasRole('Bibliotecario'))->toBeFalse()
        ->and($accessRequest->fresh()->status)->toBe('pending');
});

it('allows support to reject a pending request without assigning privileged access', function (): void {
    $user = User::factory()->create();
    $accessRequest = $user->librarianAccessRequest()->create();
    $support = User::factory()->create();
    $support->assignRole('Soporte');

    $this->actingAs($support)
        ->post(route('support.librarian-requests.reject', $accessRequest))
        ->assertRedirect(route('support.dashboard'));

    $this->assertDatabaseHas('librarian_access_requests', [
        'id' => $accessRequest->id,
        'status' => 'rejected',
        'reviewed_by' => $support->id,
    ]);

    expect($user->fresh()->hasRole('Bibliotecario'))->toBeFalse();

    $this->actingAs($user)
        ->post(route('librarian-access-requests.store'), [
            'request_type' => 'new',
            'proposed_library_name' => 'Nueva propuesta',
            'proposed_library_location' => 'Sevilla',
        ])
        ->assertRedirect(route('dashboard'));

    expect($user->fresh()->librarianAccessRequest->status)->toBe('pending')
        ->and($user->fresh()->hasRole('Bibliotecario'))->toBeFalse();
});

it('prevents users without user management permission from reviewing librarian access requests', function (): void {
    $accessRequest = User::factory()->create()->librarianAccessRequest()->create();
    $client = User::factory()->create();
    $client->assignRole('Cliente');
    $contentManager = User::factory()->create();
    $contentManager->assignRole('Gestor de contenidos');

    $this->actingAs($client)
        ->post(route('support.librarian-requests.approve', $accessRequest))
        ->assertForbidden();

    $this->actingAs($contentManager)
        ->post(route('support.librarian-requests.approve', $accessRequest))
        ->assertForbidden();

    $this->assertDatabaseHas('librarian_access_requests', [
        'id' => $accessRequest->id,
        'status' => 'pending',
    ]);
});

it('does not process a librarian request more than once', function (): void {
    $user = User::factory()->create();
    $accessRequest = $user->librarianAccessRequest()->create([
        'status' => 'approved',
    ]);
    $support = User::factory()->create();
    $support->assignRole('Soporte');

    $this->actingAs($support)
        ->post(route('support.librarian-requests.approve', $accessRequest), [
            'library_id' => Library::factory()->create()->id,
        ])
        ->assertConflict();
});
