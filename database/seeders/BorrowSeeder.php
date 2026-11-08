<?php

namespace Database\Seeders;

use App\Models\Borrow;
use App\Models\Copie;
use App\Models\User;
use Illuminate\Database\Seeder;

class BorrowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Borrow::query()->exists()) {
            return;
        }

        $availableCopies = Copie::query()
            ->whereHas('copyStatus', fn ($query) => $query->where('name', 'Available'))
            ->orderBy('id')
            ->get();

        if ($availableCopies->isEmpty()) {
            return;
        }

        $user = User::query()->orderBy('id')->firstOrFail();
        $borrowCount = (int) ceil($availableCopies->count() / 3);

        foreach ($availableCopies->take($borrowCount) as $copy) {
            Borrow::query()->create([
                'copy_id' => $copy->id,
                'user_id' => $user->id,
                'loaned_at' => '2026-11-06 12:00:00',
                'deadline' => '2026-11-21',
            ]);
        }
    }
}
