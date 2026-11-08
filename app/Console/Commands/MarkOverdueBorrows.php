<?php

namespace App\Console\Commands;

use App\Models\Borrow;
use App\Models\BorrowStatus;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

#[Signature('borrows:mark-overdue')]
#[Description('Mark active loans past their deadline as overdue')]
class MarkOverdueBorrows extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $overdueStatusId = BorrowStatus::query()
            ->where('name', 'Overdue')
            ->firstOrFail()
            ->getKey();
        $updatedBorrowCount = 0;

        Borrow::query()
            ->whereNull('returned_at')
            ->whereDate('deadline', '<', today()->toDateString())
            ->where('borrow_status_id', '!=', $overdueStatusId)
            ->orderBy('id')
            ->chunkById(100, function (Collection $borrows) use (&$updatedBorrowCount): void {
                foreach ($borrows as $borrow) {
                    if ($borrow->markAsOverdue()) {
                        $updatedBorrowCount++;
                    }
                }
            });

        $this->info("Updated {$updatedBorrowCount} overdue loan(s).");

        return self::SUCCESS;
    }
}
