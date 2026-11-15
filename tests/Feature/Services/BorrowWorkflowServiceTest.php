<?php

use App\Models\Borrow;
use App\Models\Copie;
use App\Models\Reserve;
use App\Services\BorrowWorkflowService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

it('marks overdue loans and invokes the overdue notification command', function (): void {
    $this->travelTo(new \DateTimeImmutable('2026-11-01 12:00:00'));
    $borrow = Borrow::factory()->create(['deadline' => '2026-11-02']);
    $this->travelTo(new \DateTimeImmutable('2026-11-03 12:00:00'));
    DB::table('borrows')->where('id', $borrow->id)->update(['deadline' => '2026-11-02']);
    Log::spy();

    $updatedBorrowCount = app(BorrowWorkflowService::class)->markOverdueBorrows();

    expect($updatedBorrowCount)->toBe(1)
        ->and($borrow->fresh()->borrowStatus->name)->toBe('Overdue');

    Log::shouldHaveReceived('info')->once()->with(
        'Future overdue loan email notification prepared.',
        ['borrow_id' => $borrow->id, 'user_id' => $borrow->user_id],
    );
});

it('marks a loan as returned and invokes the reservation-holder notification command', function (): void {
    $copy = Copie::factory()->available()->create();
    $reserve = Reserve::factory()
        ->for($copy, 'copy')
        ->create([
            'start_date' => now()->subHour(),
            'expiration_date' => now()->addHour(),
        ]);
    $borrow = Borrow::factory()->for($copy, 'copy')->create();
    Log::spy();

    app(BorrowWorkflowService::class)->markAsReturned($borrow);

    expect($borrow->fresh()->returned_at)->not->toBeNull()
        ->and($copy->fresh()->copyStatus->name)->toBe('Reserved');

    Log::shouldHaveReceived('info')->once()->with(
        'Future reservation-holder email notification prepared.',
        [
            'copy_id' => $copy->id,
            'reservation_id' => $reserve->id,
            'user_id' => $reserve->user_id,
        ],
    );
});
