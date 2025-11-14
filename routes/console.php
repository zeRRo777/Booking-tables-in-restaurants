<?php

use App\Console\Commands\AggregateRestaurantStats;
use App\Console\Commands\PruneOldRestaurantSchedules;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command(PruneOldRestaurantSchedules::class)->daily();

Schedule::command(AggregateRestaurantStats::class)->dailyAt('23:45');
