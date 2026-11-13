<?php

use App\Models\Copie;
use App\Models\Reserve;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

it('logs a notification for the holder of an active reservation on an available copy', function (): void {
    $copy = Copie::factory()->available()->create();
    $reserve = Reserve::factory()->for($copy, 'copy')->create();

    Log::spy();

    $this->artisan('reserves:notify-holders', ['copy' => $copy->id])
        ->assertSuccessful()
        ->expectsOutput('Logged 1 reservation-holder notification(s).');

    Log::shouldHaveReceived('info')->once()->with(
        'Future reservation-holder email notification prepared.',
        [
            'copy_id' => $copy->id,
            'reservation_id' => $reserve->id,
            'user_id' => $reserve->user_id,
        ],
    );
});

it('fails when the requested copy does not exist', function (): void {
    $this->artisan('reserves:notify-holders', ['copy' => 999999])
        ->expectsOutput('Copy 999999 was not found.')
        ->assertExitCode(1);
});
