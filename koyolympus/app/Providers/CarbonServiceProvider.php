<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\ServiceProvider;

class CarbonServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Carbon::macro('startOfLastWeek', function (CarbonImmutable $now) {
            return $now->subWeek()->startOfWeek(0);
        });

        Carbon::macro('endOfLastWeek', function (CarbonImmutable $now) {
            return $now->subWeek()->endOfWeek(6);
        });

        Carbon::macro('startOfLastMonth', function (CarbonImmutable $now) {
            return $now->subMonthNoOverflow()->startOfMonth();
        });

        Carbon::macro('endOfLastMonth', function (CarbonImmutable $now) {
            return $now->subMonthNoOverflow()->endOfMonth();
        });

        Carbon::macro('isFirstDayOfMonth', function (CarbonImmutable $now) {
            return $now->day === 1;
        });
    }
}
