<?php

use App\Models\Borrow;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

it('logs a notification for a selected active overdue loan', function (): void {
    $this->travelTo(new \DateTimeImmutable('2026-11-01 12:00:00'));
    $borrow = Borrow::factory()->create(['deadline' => '2026-11-02']);
    $this->travelTo(new \DateTimeImmutable('2026-11-03 12:00:00'));
    DB::table('borrows')->where('id', $borrow->id)->update(['deadline' => '2026-11-02']);
    $borrow->refresh()->markAsOverdue();

    Log::spy();

    $this->artisan('borrows:notify-overdue', ['borrow' => $borrow->id])
        ->assertSuccessful()
        ->expectsOutput('Logged 1 overdue loan notification(s).');

    Log::shouldHaveReceived('info')->once()->with(
        'Future overdue loan email notification prepared.',
        ['borrow_id' => $borrow->id, 'user_id' => $borrow->user_id],
    );
});

it('fails when the requested loan does not exist', function (): void {
    $this->artisan('borrows:notify-overdue', ['borrow' => 999999])
        ->expectsOutput('Loan 999999 was not found.')
        ->assertExitCode(1);
});

it('fails when the requested loan is not overdue', function (): void {
    $borrow = Borrow::factory()->create();

    $this->artisan('borrows:notify-overdue', ['borrow' => $borrow->id])
        ->expectsOutput("Loan {$borrow->id} is not an active overdue loan.")
        ->assertExitCode(1);
});
