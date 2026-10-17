<?php

namespace Database\Seeders;

use App\Models\CopyStatus;
use Database\Factories\CopyStatusFactory;
use Illuminate\Database\Seeder;

class CopyStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (CopyStatusFactory::supportedStatuses() as $name) {// Para asegurar que se elige un estado válido de la lista de estados soportados
            CopyStatus::firstOrCreate(['name' => $name]);
        }
    }
}
