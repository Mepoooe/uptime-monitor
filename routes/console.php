<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment('Keep shipping.');
})->purpose('Display an inspiring quote');

Schedule::command('domains:check')
    ->everyMinute()
    ->withoutOverlapping(5)
    ->runInBackground();
