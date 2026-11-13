<?php

namespace App\Console\Commands;

use App\Models\Copie;
use App\Models\Reserve;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

#[Signature('reserves:notify-holders {copy?}')]
#[Description('Log future email notifications for holders of available reserved copies')]
class NotifyReservationHolders extends Command
{
    public function handle(): int
    {
        $copyId = $this->argument('copy');

        if ($copyId !== null && ! Copie::query()->whereKey($copyId)->exists()) {
            $this->error("Copy {$copyId} was not found.");

            return self::FAILURE;
        }

        $activeReservations = Reserve::query()
            ->where('start_date', '<=', now())
            ->where('expiration_date', '>', now())
            ->whereHas('copy', function (Builder $query): void {
                $query->whereDoesntHave('borrows', fn (Builder $borrowQuery) => $borrowQuery->whereNull('returned_at'));
            })
            ->when($copyId !== null, fn (Builder $query) => $query->where('copy_id', $copyId));

        $loggedNotificationCount = 0;

        $activeReservations
            ->orderBy('id')
            ->chunkById(100, function (Collection $reserves) use (&$loggedNotificationCount): void {
                foreach ($reserves as $reserve) {
                    Log::info('Future reservation-holder email notification prepared.', [
                        'copy_id' => $reserve->copy_id,
                        'reservation_id' => $reserve->getKey(),
                        'user_id' => $reserve->user_id,
                    ]);

                    $loggedNotificationCount++;
                }
            });

        $this->info("Logged {$loggedNotificationCount} reservation-holder notification(s).");

        return self::SUCCESS;
    }
}
