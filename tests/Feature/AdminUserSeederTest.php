<?php

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

it('seeds the administrator with the requested credentials and support role', function (): void {
    $this->seed(AdminUserSeeder::class);

    $administrator = User::where('email', 'a@gmail.com')->firstOrFail();

    expect($administrator->name)->toBe('Administrador')
        ->and(Hash::check('12345678', $administrator->password))->toBeTrue()
        ->and($administrator->hasRole('Soporte'))->toBeTrue()
        ->and($administrator->can('manage-users'))->toBeTrue();
});

it('updates the administrator instead of creating duplicates on reseeding', function (): void {
    $this->seed(AdminUserSeeder::class);
    $this->seed(AdminUserSeeder::class);

    expect(User::where('email', 'a@gmail.com')->count())->toBe(1);
});
