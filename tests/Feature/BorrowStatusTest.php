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

    $this->assertDatabaseCount('borrow_statuses', 3);

    expect(BorrowStatus::query()->orderBy('id')->pluck('name')->all())->toBe([
        'Active',
        'Returned',
        'Overdue',
    ]);
});

it('associates a borrow with its status through factories', function () {
    $borrow = Borrow::factory()->create();

    expect($borrow->borrowStatus->name)->toBe('Active')
        ->and($borrow->borrowStatus->borrows->modelKeys())->toBe([$borrow->id]);
});
