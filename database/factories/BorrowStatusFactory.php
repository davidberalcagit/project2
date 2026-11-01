<?php

namespace Database\Factories;

use App\Models\BorrowStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BorrowStatus>
 */
class BorrowStatusFactory extends Factory
{
    public static function supportedStatuses(): array
    {
        return [
            'Available',
            'Reserved',
            'Borrowed',
            'Expired',
            'Unavailable',
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(self::supportedStatuses()),
        ];
    }
}
