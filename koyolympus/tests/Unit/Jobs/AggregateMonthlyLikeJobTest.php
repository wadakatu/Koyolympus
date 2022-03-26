<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use Error;
use Mockery;
use Exception;
use Tests\TestCase;
use Carbon\CarbonImmutable;
use App\Http\Services\LikeService;
use App\Jobs\AggregateMonthlyLikeJob;

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
        $this->startAt = CarbonImmutable::now();

        $this->job = new AggregateMonthlyLikeJob($this->likeService, $this->startAt);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     * @throws Exception
     */
    public function handle()
    {
        $this->likeService
            ->shouldReceive('setCommandStartAt')
            ->once()
            ->with($this->startAt);

        $this->likeService
            ->shouldReceive('aggregateLikeMonthly')
            ->once();

        $this->job->handle();
    }

    /**
     * @test
     */
    public function failedException()
    {
        $throwable = new Exception('例外です！');

        $this->likeService
            ->shouldReceive('outputThrowableLog')
            ->once()
            ->with('[いいね集計・月次]', $throwable->getMessage());

        $this->likeService
            ->shouldReceive('sendThrowableMail')
            ->once()
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
            ->shouldReceive('outputThrowableLog')
            ->once()
            ->with('[いいね集計・月次]', $throwable->getMessage());

        $this->likeService
            ->shouldReceive('sendThrowableMail')
            ->once()
            ->with(
                '[Koyolympus/月次いいね集計] 例外発生のお知らせ',
                $throwable->getMessage()
            );

        $this->job->failed($throwable);
    }
}
