<?php

namespace App\Console\Commands;

use App\Services\BorrowWorkflowService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('borrows:mark-overdue')]
#[Description('Mark active loans past their deadline as overdue')]
class MarkOverdueBorrows extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(BorrowWorkflowService $borrowWorkflow): int
    {
        $updatedBorrowCount = $borrowWorkflow->markOverdueBorrows();

        $this->info("Updated {$updatedBorrowCount} overdue loan(s).");

        return self::SUCCESS;
    }
}
