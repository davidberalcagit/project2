<?php

namespace Database\Seeders;

use App\Models\BorrowStatus;
use Database\Factories\BorrowStatusFactory;
use Illuminate\Database\Seeder;

class BorrowStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (BorrowStatusFactory::supportedStatuses() as $name) {
            BorrowStatus::firstOrCreate(['name' => $name]);
        }
    }
}
