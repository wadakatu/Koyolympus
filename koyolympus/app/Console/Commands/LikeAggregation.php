<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\AggregateDailyLikeJob;
use App\Jobs\AggregateMonthlyLikeJob;
use App\Jobs\AggregateWeeklyLikeJob;
use App\Services\LikeService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class LikeAggregation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:aggregate_like';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /* @var LikeService */
    private LikeService $likeService;

    /* @var CarbonImmutable */
    private CarbonImmutable $startAt;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(LikeService $likeService)
    {
        parent::__construct();

        $this->likeService = $likeService;
        $this->startAt     = CarbonImmutable::now();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        AggregateDailyLikeJob::withChain([
            new AggregateWeeklyLikeJob($this->likeService, $this->startAt),
            new AggregateMonthlyLikeJob($this->likeService, $this->startAt),
        ])->dispatch($this->likeService, $this->startAt);

        return 0;
    }
}
