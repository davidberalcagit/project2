<?php

use App\Models\Book;
use App\Models\Library;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

it('creates the five authenticated roles with their assigned permissions', function (): void {
    $client = Role::findByName('Cliente', 'web');
    $librarian = Role::findByName('Bibliotecario', 'web');
    $internal = Role::findByName('Internos', 'web');
    $contentManager = Role::findByName('Gestor de contenidos', 'web');
    $support = Role::findByName('Soporte', 'web');

    expect(Role::query()->where('name', 'Invitado')->exists())->toBeFalse()
        ->and($client->hasPermissionTo('create-reservations'))->toBeTrue()
        ->and($client->hasPermissionTo('create-borrows'))->toBeTrue()
        ->and($librarian->hasPermissionTo('manage-copies'))->toBeTrue()
        ->and($librarian->hasPermissionTo('notify-reservation-holders'))->toBeTrue()
        ->and($librarian->hasPermissionTo('complete-borrows'))->toBeTrue()
        ->and($librarian->hasPermissionTo('complete-reservations'))->toBeTrue()
        ->and($internal->hasPermissionTo('access-library'))->toBeTrue()
        ->and($internal->hasPermissionTo('manage-reservations'))->toBeTrue()
        ->and($internal->hasPermissionTo('manage-books'))->toBeFalse()
        ->and($contentManager->hasPermissionTo('manage-books'))->toBeTrue()
        ->and($contentManager->hasPermissionTo('manage-libraries'))->toBeTrue()
        ->and($contentManager->hasPermissionTo('manage-copies'))->toBeFalse()
        ->and($support->hasPermissionTo('manage-users'))->toBeTrue()
        ->and($support->hasPermissionTo('manage-libraries'))->toBeTrue()
        ->and($support->hasPermissionTo('manage-books'))->toBeTrue();
});

it('enforces book and library creation policies across all five roles', function (): void {
    $usersByRole = [];

    foreach (['Cliente', 'Bibliotecario', 'Internos', 'Gestor de contenidos', 'Soporte'] as $roleName) {
        $user = User::factory()->create();
        $user->assignRole($roleName);
        $usersByRole[$roleName] = $user;
    }

    foreach (['Cliente', 'Bibliotecario', 'Internos'] as $roleName) {
        expect($usersByRole[$roleName]->can('create', Book::class))->toBeFalse()
            ->and($usersByRole[$roleName]->can('create', Library::class))->toBeFalse();
    }

    foreach (['Gestor de contenidos', 'Soporte'] as $roleName) {
        expect($usersByRole[$roleName]->can('create', Book::class))->toBeTrue()
            ->and($usersByRole[$roleName]->can('create', Library::class))->toBeTrue();
    }
});

it('assigns the client role to newly registered users', function (): void {
    $this->post(route('auth.register.store'), [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.test',
        'password' => 'reading-books-123',
        'password_confirmation' => 'reading-books-123',
    ])->assertRedirect(route('dashboard'));

    $user = User::where('email', 'ada@example.test')->firstOrFail();

    expect($user->hasRole('Cliente'))->toBeTrue()
        ->and($user->can('create-reservations'))->toBeTrue()
        ->and($user->can('access-support'))->toBeFalse();
});

it('restricts the librarian and support areas to their permissions', function (): void {
    $client = User::factory()->create();
    $client->assignRole('Cliente');
    $librarian = User::factory()->create();
    $librarian->assignRole('Bibliotecario');
    $librarian->library()->associate(Library::factory()->create())->save();
    $contentManager = User::factory()->create();
    $contentManager->assignRole('Gestor de contenidos');
    $support = User::factory()->create();
    $support->assignRole('Soporte');

    $this->get(route('librarian.dashboard'))
        ->assertRedirect(route('auth.login'));

    $this->actingAs($client)
        ->get(route('librarian.dashboard'))
        ->assertForbidden();

    $this->actingAs($librarian)
        ->get(route('librarian.dashboard'))
        ->assertOk()
        ->assertSee('Mi biblioteca');

    $this->actingAs($librarian)
        ->get(route('support.dashboard'))
        ->assertForbidden();

    $this->actingAs($contentManager)
        ->get(route('support.dashboard'))
        ->assertForbidden();

    $this->actingAs($support)
        ->get(route('support.dashboard'))
        ->assertOk()
        ->assertSee('Soporte');
});

it('gives existing users without a role the client role when roles are seeded', function (): void {
    $user = User::factory()->create();

    $this->seed(RolePermissionSeeder::class);

    expect($user->fresh()->hasRole('Cliente'))->toBeTrue();
});

it('can be seeded repeatedly without losing permission assignments', function (): void {
    $this->seed(RolePermissionSeeder::class);

    $librarian = Role::findByName('Bibliotecario', 'web');
    $contentManager = Role::findByName('Gestor de contenidos', 'web');

    expect($librarian->hasPermissionTo('manage-copies'))->toBeTrue()
        ->and($librarian->hasPermissionTo('complete-borrows'))->toBeTrue()
        ->and($contentManager->hasPermissionTo('manage-books'))->toBeTrue();
});

it('migrates existing invited users to the client role', function (): void {
    $invited = Role::query()->create([
        'name' => 'Invitado',
        'guard_name' => 'web',
    ]);
    $user = User::factory()->create();
    $user->assignRole($invited);

    $this->seed(RolePermissionSeeder::class);

    expect($user->fresh()->hasRole('Cliente'))->toBeTrue()
        ->and($user->fresh()->hasRole('Invitado'))->toBeFalse()
        ->and(Role::query()->where('name', 'Invitado')->exists())->toBeFalse();
});
