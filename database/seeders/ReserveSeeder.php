<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Borrow;
use App\Models\Copie;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BorrowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $copie = Copie::all();
        $user = User::all();
        foreach ($copie as $copies) {
            foreach ($user as $users) {
                Borrow::factory($copies)->create([
                    'copy_id' => $copies->id,
                    'user_id' => $users->id,
                    'deadline' => now()->addDay(15)
                ]);
            }
        }
    }
}
