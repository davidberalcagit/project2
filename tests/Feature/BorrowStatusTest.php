<?php

use App\Models\Borrow;
use App\Models\BorrowStatus;
use Database\Seeders\BorrowStatusSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

it('seeds the supported borrow statuses in English', function () {
    $this->seed(BorrowStatusSeeder::class);
    $this->seed(BorrowStatusSeeder::class);

    $this->assertDatabaseCount('borrow_statuses', 5);

    expect(BorrowStatus::query()->orderBy('id')->pluck('name')->all())->toBe([
        'Available',
        'Reserved',
        'Borrowed',
        'Expired',
        'Unavailable',
    ]);
});

it('associates a borrow with its status through factories', function () {
    $borrow = Borrow::factory()->create();

    expect($borrow->borrowStatus->name)->toBeIn(BorrowStatus::query()->pluck('name')->all())
        ->and($borrow->borrowStatus->borrows->modelKeys())->toBe([$borrow->id]);
});
