<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('warranty:sync-statuses')
    ->dailyAt('00:15')
    ->withoutOverlapping();
