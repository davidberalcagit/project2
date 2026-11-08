<?php

namespace Database\Factories;

use App\Models\Borrow;
use App\Models\BorrowStatus;
use App\Models\Copie;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Borrow>
 */
class BorrowFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'copy_id' => Copie::factory(),
            'borrow_status_id' => fn (): int => BorrowStatus::query()
                ->firstOrCreate(['name' => 'Active'])
                ->getKey(),
            'deadline' => fake()->dateTimeBetween('+1 week', '+1 month')->format('Y-m-d'),
        ];
    }
}
