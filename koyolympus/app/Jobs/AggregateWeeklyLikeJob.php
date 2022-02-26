<?php

namespace App\Jobs;

use Throwable;
use Exception;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use App\Http\Services\LikeService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AggregateWeeklyLikeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 最大試行回数
     *
     * @var int
     */
    public $tries = 5;

    /* @var LikeService */
    private $likeService;

    /* @var CarbonImmutable */
    private $startAt;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(LikeService $likeService, CarbonImmutable $startAt)
    {
        $this->likeService = $likeService;
        $this->startAt = $startAt;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $this->likeService->setCommandStartAt($this->startAt);
        $this->likeService->aggregateLikeWeekly();
    }

    /**
     * 失敗したジョブの処理
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {

    }
}
