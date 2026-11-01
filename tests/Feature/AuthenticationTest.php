<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

it('keeps the catalog public and renders authentication forms for guests', function (): void {
    $this->get(route('home'))->assertOk();
    $this->get(route('books.index'))->assertOk();
    $this->get(route('libraries.index'))->assertOk();
    $this->get(route('auth.login'))->assertOk()->assertSee('Iniciar sesión');
    $this->get(route('auth.register'))->assertOk()->assertSee('Crear cuenta');
});

it('redirects guests to login when they open the private dashboard', function (): void {
    $this->get(route('dashboard'))
        ->assertRedirect(route('auth.login'));
});

it('creates an account and signs the user in', function (): void {
    $response = $this->post(route('auth.register.store'), [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.test',
        'password' => 'reading-books-123',
        'password_confirmation' => 'reading-books-123',
    ]);

    $user = User::where('email', 'ada@example.test')->firstOrFail();

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseHas('users', ['email' => 'ada@example.test']);
    expect(Hash::check('reading-books-123', $user->password))->toBeTrue();
});

it('rejects invalid registration data', function (): void {
    $this->post(route('auth.register.store'), [
        'name' => '',
        'email' => 'not-an-email',
        'password' => 'short',
        'password_confirmation' => 'different',
    ])
        ->assertSessionHasErrors(['name', 'email', 'password']);

    $this->assertDatabaseCount('users', 0);
    $this->assertGuest();
});

it('authenticates users with valid credentials', function (): void {
    $user = User::factory()->create([
        'email' => 'ada@example.test',
        'password' => Hash::make('reading-books-123'),
    ]);

    $this->post(route('auth.login.store'), [
        'email' => 'ada@example.test',
        'password' => 'reading-books-123',
    ])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid login credentials', function (): void {
    User::factory()->create([
        'email' => 'ada@example.test',
        'password' => Hash::make('reading-books-123'),
    ]);

    $this->from(route('auth.login'))
        ->post(route('auth.login.store'), [
            'email' => 'ada@example.test',
            'password' => 'incorrect-password',
        ])
        ->assertRedirect(route('auth.login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('allows authenticated users into the dashboard and logs them out', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Zona privada')
        ->assertSee($user->name);

    $this->post(route('auth.logout'))
        ->assertRedirect(route('home'));

    $this->assertGuest();
});

it('limits repeated login attempts', function (): void {
    User::factory()->create([
        'email' => 'ada@example.test',
        'password' => Hash::make('reading-books-123'),
    ]);

    foreach (range(1, 5) as $attempt) {
        $this->post(route('auth.login.store'), [
            'email' => 'ada@example.test',
            'password' => 'incorrect-password',
        ]);
    }

    $this->post(route('auth.login.store'), [
        'email' => 'ada@example.test',
        'password' => 'incorrect-password',
    ])->assertTooManyRequests();
});
