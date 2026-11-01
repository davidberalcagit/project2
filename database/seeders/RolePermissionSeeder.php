<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rolePermissions = [
            'Cliente' => [
                'view-catalog',
                'view-library-stock',
                'create-reservations',
                'create-borrows',
            ],
            'Bibliotecario' => [
                'view-catalog',
                'view-library-stock',
                'access-library',
                'manage-copies',
                'manage-borrows',
                'manage-reservations',
                'notify-reservation-holders',
                'complete-borrows',
                'complete-reservations',
            ],
            'Internos' => [
                'view-catalog',
                'view-library-stock',
                'access-library',
                'manage-copies',
                'manage-borrows',
                'manage-reservations',
                'notify-reservation-holders',
                'complete-borrows',
                'complete-reservations',
            ],
            'Gestor de contenidos' => [
                'view-catalog',
                'view-library-stock',
                'manage-books',
                'manage-libraries',
            ],
            'Soporte' => [
                'view-catalog',
                'view-library-stock',
                'access-library',
                'manage-copies',
                'manage-borrows',
                'manage-reservations',
                'notify-reservation-holders',
                'complete-borrows',
                'complete-reservations',
                'access-support',
                'manage-users',
                'manage-libraries',
                'manage-books',
            ],
        ];

        foreach (array_unique(array_merge(...array_values($rolePermissions))) as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($rolePermissions as $roleName => $permissions) {
            Role::query()->firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ])->syncPermissions($permissions);
        }

        User::query()
            ->whereHas('roles', function ($query): void {
                $query->where('name', 'Invitado')
                    ->where('guard_name', 'web');
            })
            ->each(function (User $user): void {
                $user->assignRole('Cliente');
            });

        Role::query()
            ->where('name', 'Invitado')
            ->where('guard_name', 'web')
            ->delete();

        User::query()
            ->whereDoesntHave('roles')
            ->each(function (User $user): void {
                $user->assignRole('Cliente');
            });
    }
}
