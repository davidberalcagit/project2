<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('borrows:mark-overdue')
    ->dailyAt('00:01')
    ->withoutOverlapping(30);

Schedule::command('copies:sync-availability')
    ->everyMinute()
    ->withoutOverlapping(30);
