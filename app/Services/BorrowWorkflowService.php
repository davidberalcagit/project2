<?php

namespace App\Services;

use App\Models\Borrow;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BorrowWorkflowService
{
    public function markOverdueBorrows(): int
    {
        $updatedBorrowCount = 0;

        Borrow::query()
            ->overdue()
            ->orderBy('id')
            ->chunkById(100, function (Collection $borrows) use (&$updatedBorrowCount): void {
                foreach ($borrows as $borrow) {
                    if ($this->markAsOverdue($borrow)) {
                        $updatedBorrowCount++;
                    }
                }
            });

        return $updatedBorrowCount;
    }

    public function markAsOverdue(Borrow $borrow): bool
    {
        $wasMarkedOverdue = DB::transaction(fn (): bool => $borrow->markAsOverdue());

        if ($wasMarkedOverdue) {
            $this->callCommand('borrows:notify-overdue', [
                'borrow' => $borrow->getKey(),
            ]);
        }

        return $wasMarkedOverdue;
    }

    public function markAsReturned(Borrow $borrow): void
    {
        $borrow->markAsReturned();

        $this->callCommand('reserves:notify-holders', [
            'copy' => $borrow->copy_id,
        ]);
    }

    /**
     * @param  array<string, int|string>  $arguments
     */
    private function callCommand(string $command, array $arguments): void
    {
        if (Artisan::call($command, $arguments) !== Command::SUCCESS) {
            throw new RuntimeException("The {$command} command failed.");
        }
    }
}
