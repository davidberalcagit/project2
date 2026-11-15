<?php

namespace App\Console\Commands;

use App\Models\Borrow;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

#[Signature('borrows:daily-closing {--date= : Closing date in YYYY-MM-DD format}')]
#[Description('Prepare and log a daily summary of loans')]
class PrepareDailyBorrowClosing extends Command
{
    public function handle(): int
    {
        $date = $this->option('date') ?? today()->toDateString();

        if (Validator::make(['date' => $date], ['date' => ['required', 'date_format:Y-m-d']])->fails()) {
            $this->error('Closing date must use YYYY-MM-DD format.');

            return self::FAILURE;
        }

        $closingDate = Carbon::createFromFormat('!Y-m-d', $date);
        $startOfDay = $closingDate->copy()->startOfDay();
        $endOfDay = $closingDate->copy()->endOfDay();
        $issuedLoanCount = Borrow::query()
            ->whereBetween('loaned_at', [$startOfDay, $endOfDay])
            ->count();
        $returnedLoanCount = Borrow::query()
            ->whereBetween('returned_at', [$startOfDay, $endOfDay])
            ->count();
        $overdueLoanCount = Borrow::query()
            ->whereDate('deadline', '<', $closingDate->toDateString())
            ->where(function (Builder $query) use ($endOfDay): void {
                $query->whereNull('returned_at')
                    ->orWhere('returned_at', '>', $endOfDay);
            })
            ->count();

        Log::info('Daily loan closing prepared.', [
            'closing_date' => $closingDate->toDateString(),
            'issued_loans' => $issuedLoanCount,
            'returned_loans' => $returnedLoanCount,
            'overdue_loans' => $overdueLoanCount,
        ]);

        $this->info(
            "Daily loan closing for {$closingDate->toDateString()}: "
            ."{$issuedLoanCount} issued, {$returnedLoanCount} returned, {$overdueLoanCount} overdue."
        );

        return self::SUCCESS;
    }
}
