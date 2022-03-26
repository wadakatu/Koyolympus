<?php

declare(strict_types=1);

namespace App\Jobs;

use Throwable;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use App\Http\Services\LikeService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AggregateMonthlyLikeJob implements ShouldQueue
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
     * @throws \Exception
     */
    public function handle()
    {
        $this->likeService->setCommandStartAt($this->startAt);
        $this->likeService->aggregateLikeMonthly();
    }

    /**
     * 失敗したジョブの処理
     *
     * @param Throwable $throwable
     * @return void
     */
    public function failed(Throwable $throwable)
    {
        $this->likeService->outputThrowableLog('[いいね集計・月次]', $throwable->getMessage());

        $this->likeService->sendThrowableMail(
            '[Koyolympus/月次いいね集計] 例外発生のお知らせ',
            $throwable->getMessage(),
        );
    }
}
