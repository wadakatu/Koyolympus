<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\LikeService;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class AggregateWeeklyLikeJob implements ShouldQueue
{
    use Queueable;
    use Dispatchable;
    use SerializesModels;
    use InteractsWithQueue;

    /**
     * 最大試行回数
     *
     * @var int
     */
    public int $tries = 5;

    /* @var LikeService */
    private LikeService $likeService;

    /* @var CarbonImmutable */
    private CarbonImmutable $startAt;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(LikeService $likeService, CarbonImmutable $startAt)
    {
        $this->likeService = $likeService;
        $this->startAt     = $startAt;
    }

    /**
     * Execute the job.
     *
     * @return void
     *
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
     * @param  Throwable  $throwable
     * @return void
     */
    public function failed(Throwable $throwable)
    {
        $this->likeService->outputThrowableLog('[いいね集計・週次]', $throwable->getMessage());

        $this->likeService->sendThrowableMail(
            '[Koyolympus/週次いいね集計] 例外発生のお知らせ',
            $throwable->getMessage(),
        );
    }
}
