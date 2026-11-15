<?php

use App\Models\Borrow;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

it('returns only borrows that have not been returned', function () {
    $activeBorrow = Borrow::factory()->create();
    Borrow::factory()->create(['returned_at' => now()->addMinute()]);

    $activeBorrowIds = Borrow::active()->pluck('id')->all();

    expect($activeBorrowIds)->toBe([$activeBorrow->id]);
});

it('returns unreturned borrows whose deadline has passed', function () {
    $this->travelTo(new DateTimeImmutable('2026-01-15 12:00:00'));

    $overdueBorrow = Borrow::factory()->create(['deadline' => '2026-01-16']);
    $onTimeBorrow = Borrow::factory()->create(['deadline' => '2026-01-16']);

    DB::table('borrows')->where('id', $overdueBorrow->id)->update([
        'deadline' => '2026-01-14',
    ]);

    $overdueBorrowIds = Borrow::overdue()->pluck('id')->all();

    expect($overdueBorrowIds)->toBe([$overdueBorrow->id])
        ->and($onTimeBorrow->id)->not->toBeIn($overdueBorrowIds);
});

it('filters borrows by their owning user', function () {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();
    $usersBorrow = Borrow::factory()->for($user)->create();
    Borrow::factory()->for($anotherUser)->create();

    $borrowIds = Borrow::forUser($user->id)->pluck('id')->all();

    expect($borrowIds)->toBe([$usersBorrow->id]);
});
