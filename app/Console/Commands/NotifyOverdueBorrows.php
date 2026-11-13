<?php

namespace App\Console\Commands;

use App\Models\Borrow;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

#[Signature('borrows:notify-overdue {borrow?}')]
#[Description('Log future email notifications for overdue loans')]
class NotifyOverdueBorrows extends Command
{
    public function handle(): int
    {
        $borrowId = $this->argument('borrow');

        if ($borrowId !== null && ! Borrow::query()->whereKey($borrowId)->exists()) {
            $this->error("Loan {$borrowId} was not found.");

            return self::FAILURE;
        }

        $overdueBorrows = Borrow::query()
            ->overdue()
            ->whereHas('borrowStatus', fn (Builder $query) => $query->where('name', 'Overdue'))
            ->when($borrowId !== null, fn (Builder $query) => $query->whereKey($borrowId));

        if ($borrowId !== null && ! $overdueBorrows->exists()) {
            $this->error("Loan {$borrowId} is not an active overdue loan.");

            return self::FAILURE;
        }

        $loggedNotificationCount = 0;

        $overdueBorrows
            ->orderBy('id')
            ->chunkById(100, function (Collection $borrows) use (&$loggedNotificationCount): void {
                foreach ($borrows as $borrow) {
                    Log::info('Future overdue loan email notification prepared.', [
                        'borrow_id' => $borrow->getKey(),
                        'user_id' => $borrow->user_id,
                    ]);

                    $loggedNotificationCount++;
                }
            });

        $this->info("Logged {$loggedNotificationCount} overdue loan notification(s).");

        return self::SUCCESS;
    }
}
