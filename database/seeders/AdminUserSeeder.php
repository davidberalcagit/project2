<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $administrator = User::query()->updateOrCreate(
            ['email' => 'a@gmail.com'],
            [
                'name' => 'Administrador',
                'password' => '12345678',
            ],
        );

        $administrator->syncRoles(['Soporte']);
    }
}
