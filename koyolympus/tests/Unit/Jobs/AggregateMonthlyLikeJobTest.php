<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\AggregateMonthlyLikeJob;
use App\Services\LikeService;
use Carbon\CarbonImmutable;
use Error;
use Exception;
use Mockery;
use Tests\TestCase;

class AggregateMonthlyLikeJobTest extends TestCase
{
    private $job;

    private $likeService;

    private $startAt;

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow('2021-01-01 00:00:05');

        $this->likeService = Mockery::mock(LikeService::class);
        $this->startAt     = CarbonImmutable::now();

        $this->job = new AggregateMonthlyLikeJob($this->likeService, $this->startAt);
    }

    /**
     * @test
     *
     * @throws Exception
     */
    public function handle()
    {
        $this->likeService
            ->expects('setCommandStartAt')
            ->with($this->startAt);

        $this->likeService
            ->expects('aggregateLikeMonthly');

        $this->job->handle();
    }

    /**
     * @test
     */
    public function failedException()
    {
        $throwable = new Exception('例外です！');

        $this->likeService
            ->expects('outputThrowableLog')
            ->with('[いいね集計・月次]', $throwable->getMessage());

        $this->likeService
            ->expects('sendThrowableMail')
            ->with(
                '[Koyolympus/月次いいね集計] 例外発生のお知らせ',
                $throwable->getMessage()
            );

        $this->job->failed($throwable);
    }

    /**
     * @test
     */
    public function failedError()
    {
        $throwable = new Error('例外です！');

        $this->likeService
            ->expects('outputThrowableLog')
            ->with('[いいね集計・月次]', $throwable->getMessage());

        $this->likeService
            ->expects('sendThrowableMail')
            ->with(
                '[Koyolympus/月次いいね集計] 例外発生のお知らせ',
                $throwable->getMessage()
            );

        $this->job->failed($throwable);
    }
}
