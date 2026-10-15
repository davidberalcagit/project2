<?php

namespace Database\Factories;

use App\Models\Copie;
use App\Models\Reserve;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reserve>
 */
class ReserveFactory extends Factory
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
        ];
    }
}
