<?php

namespace Database\Factories;

use App\Models\CopyStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CopyStatus>
 */
class CopyStatusFactory extends Factory
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
            'name' => fake()->randomElement(self::supportedStatuses()), // Para asegurar que se elige un estado válido de la lista de estados soportados
        ];
    }
}
