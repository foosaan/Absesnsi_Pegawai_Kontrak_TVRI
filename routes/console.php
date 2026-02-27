<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run every 30 minutes to mark overdue attendance as 'Meninggalkan Kantor'
Schedule::command('attendance:mark-left')->everyThirtyMinutes();

