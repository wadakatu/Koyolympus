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
            return $now->subDays(7)->startOfWeek(0);
        });

        Carbon::macro('endOfLastWeek', function (CarbonImmutable $now) {
            return $now->subDays(7)->endOfWeek(6);
        });
    }
}
