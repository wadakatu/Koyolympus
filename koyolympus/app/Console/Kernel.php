<?php

namespace App\Console;

use App\Console\Commands\CheckDatabase;
use App\Console\Commands\LikeAggregation;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\CheckConsistencyBetweenDBAndS3;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\ReplaceAllPhotoInfoToIncludeUuid;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        CheckDatabase::class,
        CheckConsistencyBetweenDBAndS3::class,
        ReplaceAllPhotoInfoToIncludeUuid::class,
        LikeAggregation::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(LikeAggregation::class)->dailyAt('00:05');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
