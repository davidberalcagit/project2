<?php

use App\Models\Borrow;
use App\Models\Copie;
use App\Models\Library;
use App\Models\PendingNotification;
use App\Models\Reserve;
use App\Services\BorrowWorkflowService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

it('marks overdue loans and invokes the overdue notification command', function (): void {
    $this->travelTo(new DateTimeImmutable('2026-11-01 12:00:00'));
    $borrow = Borrow::factory()->create(['deadline' => '2026-11-02']);
    $this->travelTo(new DateTimeImmutable('2026-11-03 12:00:00'));
    DB::table('borrows')->where('id', $borrow->id)->update(['deadline' => '2026-11-02']);
    Log::spy();

    $updatedBorrowCount = app(BorrowWorkflowService::class)->markOverdueBorrows();

    expect($updatedBorrowCount)->toBe(1)
        ->and($borrow->fresh()->borrowStatus->name)->toBe('Overdue');
    $this->assertDatabaseHas('pending_notifications', [
        'notification_type' => PendingNotification::TYPE_OVERDUE_BORROW,
        'deduplication_key' => 'overdue-borrow-'.$borrow->id,
        'user_id' => $borrow->user_id,
        'borrow_id' => $borrow->id,
        'status' => PendingNotification::STATUS_PENDING,
    ]);

    Log::shouldHaveReceived('info')->once()->with(
        'Future overdue loan email notification prepared.',
        ['borrow_id' => $borrow->id, 'user_id' => $borrow->user_id],
    );
});

it('keeps overdue notification pending when its follow-up command fails', function (): void {
    $this->travelTo(new DateTimeImmutable('2026-11-01 12:00:00'));
    $borrow = Borrow::factory()->create(['deadline' => '2026-11-02']);
    $this->travelTo(new DateTimeImmutable('2026-11-03 12:00:00'));
    DB::table('borrows')->where('id', $borrow->id)->update(['deadline' => '2026-11-02']);
    Artisan::shouldReceive('call')
        ->once()
        ->with('borrows:notify-overdue', ['borrow' => $borrow->id])
        ->andReturn(1);

    expect(fn () => app(BorrowWorkflowService::class)->markAsOverdue($borrow))
        ->toThrow(RuntimeException::class, 'The borrows:notify-overdue command failed.');

    expect($borrow->fresh()->borrowStatus->name)->toBe('Overdue');
    $this->assertDatabaseHas('pending_notifications', [
        'notification_type' => PendingNotification::TYPE_OVERDUE_BORROW,
        'deduplication_key' => 'overdue-borrow-'.$borrow->id,
        'user_id' => $borrow->user_id,
        'borrow_id' => $borrow->id,
        'status' => PendingNotification::STATUS_PENDING,
    ]);
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
    $this->assertDatabaseHas('pending_notifications', [
        'notification_type' => PendingNotification::TYPE_AVAILABLE_RESERVATION,
        'deduplication_key' => 'available-reservation-'.$reserve->id,
        'user_id' => $reserve->user_id,
        'reserve_id' => $reserve->id,
        'status' => PendingNotification::STATUS_PENDING,
    ]);

    Log::shouldHaveReceived('info')->once()->with(
        'Future reservation-holder email notification prepared.',
        [
            'copy_id' => $copy->id,
            'reservation_id' => $reserve->id,
            'user_id' => $reserve->user_id,
        ],
    );
});

it('records return notifications only for active reservations on the returned copy', function (): void {
    $ownLibrary = Library::factory()->create();
    $otherLibrary = Library::factory()->create();
    $ownCopy = Copie::factory()->for($ownLibrary)->available()->create();
    $otherCopy = Copie::factory()->for($otherLibrary)->available()->create();
    $ownReserve = Reserve::factory()
        ->for($ownCopy, 'copy')
        ->create([
            'start_date' => now()->subHour(),
            'expiration_date' => now()->addHour(),
        ]);
    $otherReserve = Reserve::factory()
        ->for($otherCopy, 'copy')
        ->create([
            'start_date' => now()->subHour(),
            'expiration_date' => now()->addHour(),
        ]);
    $ownBorrow = Borrow::factory()->for($ownCopy, 'copy')->create();
    $otherBorrow = Borrow::factory()->for($otherCopy, 'copy')->create();
    Log::spy();

    app(BorrowWorkflowService::class)->markAsReturned($ownBorrow);

    expect(PendingNotification::query()->get()->map(fn (PendingNotification $notification): array => [
        $notification->notification_type,
        $notification->user_id,
        $notification->reserve_id,
    ])->all())->toBe([[
        PendingNotification::TYPE_AVAILABLE_RESERVATION,
        $ownReserve->user_id,
        $ownReserve->id,
    ]]);
    expect($otherBorrow->fresh()->returned_at)->toBeNull()
        ->and($otherCopy->fresh()->copyStatus->name)->toBe('Borrowed')
        ->and($otherReserve->fresh()->id)->toBe($otherReserve->id);

    Log::shouldHaveReceived('info')->once()->with(
        'Future reservation-holder email notification prepared.',
        [
            'copy_id' => $ownCopy->id,
            'reservation_id' => $ownReserve->id,
            'user_id' => $ownReserve->user_id,
        ],
    );
});

it('keeps a return notification pending when its follow-up command fails', function (): void {
    $copy = Copie::factory()->available()->create();
    $reserve = Reserve::factory()
        ->for($copy, 'copy')
        ->create([
            'start_date' => now()->subHour(),
            'expiration_date' => now()->addHour(),
        ]);
    $borrow = Borrow::factory()->for($copy, 'copy')->create();
    Artisan::shouldReceive('call')
        ->once()
        ->with('reserves:notify-holders', ['copy' => $copy->id])
        ->andReturn(1);

    expect(fn () => app(BorrowWorkflowService::class)->markAsReturned($borrow))
        ->toThrow(RuntimeException::class, 'The reserves:notify-holders command failed.');

    expect($borrow->fresh()->returned_at)->not->toBeNull();
    $this->assertDatabaseHas('pending_notifications', [
        'notification_type' => PendingNotification::TYPE_AVAILABLE_RESERVATION,
        'deduplication_key' => 'available-reservation-'.$reserve->id,
        'user_id' => $reserve->user_id,
        'reserve_id' => $reserve->id,
        'status' => PendingNotification::STATUS_PENDING,
    ]);
});
