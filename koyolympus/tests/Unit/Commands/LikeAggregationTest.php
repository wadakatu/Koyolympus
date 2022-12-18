<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use Queue;
use Tests\TestCase;
use App\Models\Like;
use Carbon\CarbonImmutable;
use App\Traits\PrivateTrait;
use App\Services\LikeService;
use App\Models\LikeAggregate;
use Illuminate\Support\Facades\DB;
use App\Jobs\AggregateDailyLikeJob;
use App\Jobs\AggregateWeeklyLikeJob;
use App\Jobs\AggregateMonthlyLikeJob;
use App\Console\Commands\LikeAggregation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LikeAggregationTest extends TestCase
{
    use PrivateTrait;
    use RefreshDatabase;

    private $likeAggregateCommand;
    private $likeService;
    private $carbon;

    protected function setUp(): void
    {
        parent::setUp();
        CarbonImmutable::setTestNow('2022-01-01 00:00:01');

        $this->likeService = new LikeService(new Like(), new LikeAggregate());
        $this->carbon = CarbonImmutable::now();

        $this->likeAggregateCommand = new LikeAggregation($this->likeService);
    }

    /**
     * ジョブが適切にプッシュされチェインされているかテスト
     *
     * @test
     */
    public function handle()
    {
        Queue::fake();

        Queue::assertNothingPushed();

        $this->likeAggregateCommand->handle();

        Queue::assertPushed(AggregateDailyLikeJob::class, function ($job) {
            return $this->likeService === $this->getPrivateProperty($job, 'likeService')
                && $this->carbon->toDateTimeString() === $this->getPrivateProperty($job, 'startAt')->toDateTimeString();
        });

        Queue::assertPushedWithChain(AggregateDailyLikeJob::class, [
            AggregateWeeklyLikeJob::class,
            AggregateMonthlyLikeJob::class,
        ]);
    }

    /**
     * ジョブが適切にキューイングされるかテスト
     *
     * @test
     */
    public function handleQueueTest()
    {
        $this->assertFalse(DB::table('jobs')->exists());

        $this->likeAggregateCommand->handle();

        $this->assertTrue(DB::table('jobs')->exists());
        $this->assertSame(1, DB::table('jobs')->get()->count());
    }
}
