<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Book;
use App\Models\Library;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'admin',
            'email'=>'admin@gmail.com',
            'password' => ('12345678')
        ]);
        $this->call([        UserSeeder::class            ]);
        $this->call([        BookSeeder::class            ]);
        $this->call([        LibrarySeeder::class            ]);
        $this->call([        CopieSeeder::class            ]);
        $this->call([        BorrowSeeder::class            ]);
        $this->call([        ReserveSeeder::class            ]);

    }
}
