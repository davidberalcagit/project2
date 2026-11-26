<?php

namespace App\Services;

use App\Models\Borrow;
use App\Models\PendingNotification;
use App\Models\Reserve;
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
        $wasMarkedOverdue = DB::transaction(function () use ($borrow): bool {
            if (! $borrow->markAsOverdue()) {
                return false;
            }

            PendingNotification::query()->create([
                'notification_type' => PendingNotification::TYPE_OVERDUE_BORROW,
                'deduplication_key' => 'overdue-borrow-'.$borrow->getKey(),
                'user_id' => $borrow->user_id,
                'borrow_id' => $borrow->getKey(),
                'status' => PendingNotification::STATUS_PENDING,
            ]);

            return true;
        });

        if ($wasMarkedOverdue) {
            $this->callCommand('borrows:notify-overdue', [
                'borrow' => $borrow->getKey(),
            ]);
        }

        return $wasMarkedOverdue;
    }

    public function markAsReturned(Borrow $borrow): void
    {
        DB::transaction(function () use ($borrow): void {
            $borrow->markAsReturned();

            Reserve::query()
                ->where('copy_id', $borrow->copy_id)
                ->where('start_date', '<=', now())
                ->where('expiration_date', '>', now())
                ->orderBy('id')
                ->each(function (Reserve $reserve): void {
                    PendingNotification::query()->create([
                        'notification_type' => PendingNotification::TYPE_AVAILABLE_RESERVATION,
                        'deduplication_key' => 'available-reservation-'.$reserve->getKey(),
                        'user_id' => $reserve->user_id,
                        'reserve_id' => $reserve->getKey(),
                        'status' => PendingNotification::STATUS_PENDING,
                    ]);
                });
        });

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
