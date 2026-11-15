<?php

use App\Models\Borrow;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

it('logs the loan summary for the requested closing date', function (): void {
    $this->travelTo(new DateTimeImmutable('2026-11-13 12:00:00'));
    Borrow::factory()->create([
        'loaned_at' => '2026-11-13 09:00:00',
        'deadline' => '2026-11-14',
        'returned_at' => '2026-11-13 11:00:00',
    ]);
    $overdueBorrow = Borrow::factory()->create([
        'loaned_at' => '2026-11-12 09:00:00',
        'deadline' => '2026-11-14',
    ]);
    DB::table('borrows')->where('id', $overdueBorrow->id)->update([
        'deadline' => '2026-11-12',
    ]);
    Log::spy();

    $this->artisan('borrows:daily-closing', ['--date' => '2026-11-13'])
        ->assertSuccessful()
        ->expectsOutput('Daily loan closing for 2026-11-13: 1 issued, 1 returned, 1 overdue.');

    Log::shouldHaveReceived('info')->once()->with(
        'Daily loan closing prepared.',
        [
            'closing_date' => '2026-11-13',
            'issued_loans' => 1,
            'returned_loans' => 1,
            'overdue_loans' => 1,
        ],
    );
});

it('fails when the closing date is not a valid date', function (): void {
    $this->artisan('borrows:daily-closing', ['--date' => 'not-a-date'])
        ->expectsOutput('Closing date must use YYYY-MM-DD format.')
        ->assertExitCode(1);
});
