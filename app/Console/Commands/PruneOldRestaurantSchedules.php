<?php

namespace App\Console\Commands;

use App\Models\RestaurantSchedule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

class PruneOldRestaurantSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedules:prune-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove restaurant schedule records with past dates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to prune old restaurant schedules...');
        $deletedCount = RestaurantSchedule::where('date', '<', Date::today())->forceDelete();
        $this->info("Successfully deleted {$deletedCount} old schedule records.");
        return 0;
    }
}
