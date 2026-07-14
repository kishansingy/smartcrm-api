<?php

use App\Application\Task\Jobs\SendTaskReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run task reminder check every 5 minutes
Schedule::job(new SendTaskReminders)->everyFiveMinutes();
