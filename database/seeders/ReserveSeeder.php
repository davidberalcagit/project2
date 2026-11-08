<?php

namespace Database\Seeders;

use App\Models\Copie;
use App\Models\Reserve;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReserveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Reserve::query()->exists()) {
            return;
        }

        $availableCopies = Copie::query()
            ->whereHas('copyStatus', fn ($query) => $query->where('name', 'Available'))
            ->whereDoesntHave('borrows', fn ($query) => $query->whereNull('returned_at'))
            ->orderBy('id')
            ->get();

        if ($availableCopies->isEmpty()) {
            return;
        }

        $user = User::query()->orderBy('id')->firstOrFail();
        $reserveCount = (int) ceil($availableCopies->count() / 3);

        foreach ($availableCopies->take($reserveCount) as $copy) {
            Reserve::query()->create([
                'copy_id' => $copy->id,
                'user_id' => $user->id,
                'start_date' => '2026-11-06 12:00:00',
                'expiration_date' => '2026-11-07 12:00:00',
            ]);
        }
    }
}
