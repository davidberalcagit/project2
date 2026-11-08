<?php

namespace App\Console\Commands;

use App\Models\Copie;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

#[Signature('copies:sync-availability')]
#[Description('Recalculate every copy status from active loans and reservations')]
class SyncCopyAvailability extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $updatedCopyCount = 0;

        Copie::query()
            ->orderBy('id')
            ->chunkById(100, function (Collection $copies) use (&$updatedCopyCount): void {
                foreach ($copies as $copy) {
                    if ($copy->syncAvailabilityStatus()) {
                        $updatedCopyCount++;
                    }
                }
            });

        $this->info("Updated {$updatedCopyCount} copy status(es).");

        return self::SUCCESS;
    }
}
