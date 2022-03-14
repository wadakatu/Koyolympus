<?php
declare(strict_types=1);

namespace Tests\Unit\Services;

use DB;
use Mockery;
use Exception;
use Carbon\Carbon;
use Tests\TestCase;
use ReflectionException;
use App\Http\Models\Like;
use Carbon\CarbonImmutable;
use App\Mail\ThrowableMail;
use App\Traits\PrivateTrait;
use App\Http\Models\LikeAggregate;
use App\Http\Services\LikeService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Collection;

class LikeServiceTest extends TestCase
{
    use PrivateTrait;

    private $likeService;
    private $like;
    private $likeAggregate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->like = Mockery::mock(Like::class);
        $this->likeAggregate = Mockery::mock(LikeAggregate::class);

        $this->likeService = Mockery::mock(LikeService::class, [$this->like, $this->likeAggregate])->makePartial();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function setCommandStartAt()
    {
        $startAt = CarbonImmutable::parse('2021-01-01 10:01:05');

        $this->likeService->setCommandStartAt($startAt);

        $this->assertSame(
            '2021-01-01 10:01:05',
            $this->getPrivatePropertyForMockObject($this->likeService, 'startAt')->toDateTimeString()
        );
    }

    /**
     * @test
     */
    public function aggregateLikeDaily_single()
    {
        $photoId = 'test_id';
        $startAt = CarbonImmutable::parse('2021-01-01 00:00:01');

        $this->likeService->setCommandStartAt($startAt);

        $this->likeService
            ->shouldReceive('outputLog')
            ->once()
            ->with('[いいね集計・日次]', '日次いいね集計 START');

        $this->like
            ->shouldReceive('getForDailyAggregation')
            ->once()
            ->andReturn(new Collection([$likeAggregate = new LikeAggregate(['photo_id' => $photoId])]));

        $this->likeService
            ->shouldReceive('outputLog')
            ->never()
            ->with('[いいね集計・日次]', '集計対象０件のためスキップ');

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollBack')->never();

        $this->likeAggregate
            ->shouldReceive('registerForAggregation')
            ->once()
            ->with($likeAggregate, Mockery::on(function ($actual) use ($startAt) {
                $this->assertSame($startAt->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($startAt) {
                $this->assertSame($startAt->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'dailyType'));

        $this->like
            ->shouldReceive('saveByPhotoId')
            ->once()
            ->with($photoId, ['likes' => 0]);

        $this->likeService
            ->shouldReceive('outputErrorLog')
            ->never();

        $this->likeService
            ->shouldReceive('outputLog')
            ->once()
            ->with('[いいね集計・日次]', '日次いいね集計 END');

        $this->likeService->aggregateLikeDaily();
    }

    /**
     * @test
     */
    public function aggregateLikeDaily_empty()
    {
        $startAt = CarbonImmutable::parse('2021-01-01 00:00:01');

        $this->likeService->setCommandStartAt($startAt);

        $this->likeService
            ->shouldReceive('outputLog')
            ->once()
            ->with('[いいね集計・日次]', '日次いいね集計 START');

        $this->like
            ->shouldReceive('getForDailyAggregation')
            ->once()
            ->andReturn(new Collection());

        $this->likeService
            ->shouldReceive('outputLog')
            ->once()
            ->with('[いいね集計・日次]', '集計対象０件のためスキップ');

        DB::shouldReceive('beginTransaction')->never();
        DB::shouldReceive('commit')->never();
        DB::shouldReceive('rollBack')->never();

        $this->likeAggregate
            ->shouldReceive('registerForAggregation')
            ->never();

        $this->like
            ->shouldReceive('saveByPhotoId')
            ->never();

        $this->likeService
            ->shouldReceive('outputLog')
            ->never()
            ->with('[いいね集計・日次]', '日次いいね集計 END');
        $this->likeService
            ->shouldReceive('outputErrorLog')
            ->never();

        $this->likeService->aggregateLikeDaily();
    }

    /**
     * @test
     */
    public function aggregateLikeDaily_multiple()
    {
        $photoId1 = 'photo1';
        $likeAggregate1 = new LikeAggregate(['photo_id' => $photoId1]);
        $photoId2 = 'photo2';
        $likeAggregate2 = new LikeAggregate(['photo_id' => $photoId2]);
        $photoId3 = 'photo3';
        $likeAggregate3 = new LikeAggregate(['photo_id' => $photoId3]);

        $startAt = CarbonImmutable::parse('2021-01-01 00:00:01');

        $this->likeService->setCommandStartAt($startAt);

        $this->likeService
            ->shouldReceive('outputLog')
            ->once()
            ->with('[いいね集計・日次]', '日次いいね集計 START');

        $this->like
            ->shouldReceive('getForDailyAggregation')
            ->once()
            ->andReturn(new Collection([$likeAggregate1, $likeAggregate2, $likeAggregate3]));

        $this->likeService
            ->shouldReceive('outputLog')
            ->never()
            ->with('[いいね集計・日次]', '集計対象０件のためスキップ');

        DB::shouldReceive('beginTransaction')->times(3);
        DB::shouldReceive('commit')->times(3);
        DB::shouldReceive('rollBack')->never();

        $this->likeAggregate
            ->shouldReceive('registerForAggregation')
            ->once()
            ->with($likeAggregate1, Mockery::on(function ($actual) use ($startAt) {
                $this->assertSame($startAt->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($startAt) {
                $this->assertSame($startAt->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'dailyType'));
        $this->likeAggregate
            ->shouldReceive('registerForAggregation')
            ->once()
            ->with($likeAggregate2, Mockery::on(function ($actual) use ($startAt) {
                $this->assertSame($startAt->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($startAt) {
                $this->assertSame($startAt->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'dailyType'));
        $this->likeAggregate
            ->shouldReceive('registerForAggregation')
            ->once()
            ->with($likeAggregate3, Mockery::on(function ($actual) use ($startAt) {
                $this->assertSame($startAt->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($startAt) {
                $this->assertSame($startAt->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'dailyType'));

        $this->like
            ->shouldReceive('saveByPhotoId')
            ->once()
            ->with($photoId1, ['likes' => 0]);
        $this->like
            ->shouldReceive('saveByPhotoId')
            ->once()
            ->with($photoId2, ['likes' => 0]);
        $this->like
            ->shouldReceive('saveByPhotoId')
            ->once()
            ->with($photoId3, ['likes' => 0]);

        $this->likeService
            ->shouldReceive('outputErrorLog')
            ->never();

        $this->likeService
            ->shouldReceive('outputLog')
            ->once()
            ->with('[いいね集計・日次]', '日次いいね集計 END');

        $this->likeService->aggregateLikeDaily();
    }

    /**
     * @test
     */
    public function aggregateLikeDaily_single_exception()
    {
        $photoId = 'test_id';
        $startAt = CarbonImmutable::parse('2021-01-01 00:00:01');
        $exception = new Exception('例外発生！');

        $this->likeService->setCommandStartAt($startAt);

        $this->likeService
            ->shouldReceive('outputLog')
            ->once()
            ->with('[いいね集計・日次]', '日次いいね集計 START');

        $this->like
            ->shouldReceive('getForDailyAggregation')
            ->once()
            ->andReturn(new Collection([$likeAggregate = new LikeAggregate(['photo_id' => $photoId])]));

        $this->likeService
            ->shouldReceive('outputLog')
            ->never()
            ->with('[いいね集計・日次]', '集計対象０件のためスキップ');

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->never();
        DB::shouldReceive('rollBack')->once();

        $this->likeAggregate
            ->shouldReceive('registerForAggregation')
            ->once()
            ->with($likeAggregate, Mockery::on(function ($actual) use ($startAt) {
                $this->assertSame($startAt->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($startAt) {
                $this->assertSame($startAt->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'dailyType'))
            ->andThrow($exception);

        $this->like
            ->shouldReceive('saveByPhotoId')
            ->never()
            ->with($photoId, ['likes' => 0]);

        $this->likeService
            ->shouldReceive('outputErrorLog')
            ->once()
            ->with('[いいね集計・日次]', "例外発生　対象：$photoId");

        $this->likeService
            ->shouldReceive('outputLog')
            ->never()
            ->with('[いいね集計・日次]', '日次いいね集計 END');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('例外発生！');

        $this->likeService->aggregateLikeDaily();
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function aggregateLikeDaily_multiple_exception()
    {
        $photoId1 = 'photo1';
        $likeAggregate1 = new LikeAggregate(['photo_id' => $photoId1]);
        $photoId2 = 'photo2';
        $likeAggregate_exception = new LikeAggregate(['photo_id' => $photoId2]);
        $photoId3 = 'photo3';
        $likeAggregate3 = new LikeAggregate(['photo_id' => $photoId3]);
        $exception = new Exception('例外発生！');

        $startAt = CarbonImmutable::parse('2021-01-01 00:00:01');

        $this->likeService->setCommandStartAt($startAt);

        $this->likeService
            ->shouldReceive('outputLog')
            ->once()
            ->with('[いいね集計・日次]', '日次いいね集計 START');

        $this->like
            ->shouldReceive('getForDailyAggregation')
            ->once()
            ->andReturn(new Collection([$likeAggregate1, $likeAggregate_exception, $likeAggregate3]));

        $this->likeService
            ->shouldReceive('outputLog')
            ->never()
            ->with('[いいね集計・日次]', '集計対象０件のためスキップ');

        DB::shouldReceive('beginTransaction')->times(2);
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollBack')->once();

        $this->likeAggregate
            ->shouldReceive('registerForAggregation')
            ->once()
            ->with($likeAggregate1, Mockery::on(function ($actual) use ($startAt) {
                $this->assertSame($startAt->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($startAt) {
                $this->assertSame($startAt->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'dailyType'));
        $this->likeAggregate
            ->shouldReceive('registerForAggregation')
            ->once()
            ->with($likeAggregate_exception, Mockery::on(function ($actual) use ($startAt) {
                $this->assertSame($startAt->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($startAt) {
                $this->assertSame($startAt->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'dailyType'))
            ->andThrow($exception);
        $this->likeAggregate
            ->shouldReceive('registerForAggregation')
            ->never()
            ->with($likeAggregate3, Mockery::on(function ($actual) use ($startAt) {
                $this->assertSame($startAt->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($startAt) {
                $this->assertSame($startAt->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'dailyType'));

        $this->like
            ->shouldReceive('saveByPhotoId')
            ->once()
            ->with($photoId1, ['likes' => 0]);
        $this->like
            ->shouldReceive('saveByPhotoId')
            ->never()
            ->with($photoId2, ['likes' => 0]);
        $this->like
            ->shouldReceive('saveByPhotoId')
            ->never()
            ->with($photoId3, ['likes' => 0]);

        $this->likeService
            ->shouldReceive('outputErrorLog')
            ->once()
            ->with('[いいね集計・日次]', "例外発生　対象：$photoId2");

        $this->likeService
            ->shouldReceive('outputLog')
            ->never()
            ->with('[いいね集計・日次]', '日次いいね集計 END');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('例外発生！');

        $this->likeService->aggregateLikeDaily();
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function aggregateLikeWeekly_single()
    {
        $photoId = 'test_id';
        $likeAggregate = new LikeAggregate(['photo_id' => $photoId, 'likes' => 10]);
        $startAt = CarbonImmutable::parse('2021-01-03 00:00:01');
        $startOfLastWeek = Carbon::startOfLastWeek($startAt);
        $endOfLastWeek = Carbon::endOfLastWeek($startAt);
        $dayOfWeek = $startAt->isoFormat('dddd');

        $this->likeService->setCommandStartAt($startAt);

        $this->likeService
            ->shouldReceive('outputLog')
            ->never()
            ->with('[いいね集計・週次]', "本日 $dayOfWeek なのでスキップ");

        $this->likeAggregate
            ->shouldReceive('getForAggregation')
            ->once()
            ->with(Mockery::on(function ($actual) use ($startOfLastWeek) {
                $this->assertSame($startOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastWeek) {
                $this->assertSame($endOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'dailyType'))
            ->andReturn(new Collection([$likeAggregate]));

        $this->likeService
            ->shouldReceive('outputLog')
            ->once()
            ->with('[いいね集計・週次]', '週次いいね集計 START');

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollBack')->never();

        $this->likeService
            ->shouldReceive('registerForWeeklyAggregation')
            ->once()
            ->with(Mockery::on(function ($actual) use ($likeAggregate) {
                $this->assertSame($likeAggregate->toArray(), $actual[0]->toArray());
                return true;
            }), Mockery::on(function ($actual) use ($startOfLastWeek) {
                $this->assertSame($startOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastWeek) {
                $this->assertSame($endOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }));

        $this->like
            ->shouldReceive('saveByPhotoId')
            ->once()
            ->with($photoId, ['weekly_likes' => 10]);

        $this->likeService
            ->shouldReceive('updateForWeeklyAggregation')
            ->once()
            ->with(Mockery::on(function ($actual) use ($likeAggregate) {
                $this->assertSame($likeAggregate->toArray(), $actual[0]->toArray());
                return true;
            }), Mockery::on(function ($actual) use ($startOfLastWeek) {
                $this->assertSame($startOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastWeek) {
                $this->assertSame($endOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }));

        $this->likeService
            ->shouldReceive('outputErrorLog')
            ->never()
            ->with('[いいね集計・週次]', "例外発生 対象：$photoId");

        $this->likeService
            ->shouldReceive('outputLog')
            ->once()
            ->with('[いいね集計・週次]', '週次いいね集計 END');

        $this->likeService->aggregateLikeWeekly();
    }

    /**
     * @test
     * @dataProvider providerAggregateLikeWeekly_isNotSunday
     */
    public function aggregateLikeWeekly_isNotSunday($date, $dayOfWeek)
    {
        $photoId = 'test_id';
        $startAt = CarbonImmutable::parse($date);
        $this->likeService->setCommandStartAt($startAt);

        $this->likeService
            ->shouldReceive('outputLog')
            ->once()
            ->with('[いいね集計・週次]', "本日 $dayOfWeek なのでスキップ");

        $this->likeAggregate
            ->shouldReceive('getForAggregation')
            ->never();

        $this->likeService
            ->shouldReceive('outputLog')
            ->never()
            ->with('[いいね集計・週次]', '週次いいね集計 START');

        DB::shouldReceive('beginTransaction')->never();
        DB::shouldReceive('commit')->never();
        DB::shouldReceive('rollBack')->never();

        $this->likeService
            ->shouldReceive('registerForWeeklyAggregation')
            ->never();

        $this->like
            ->shouldReceive('saveByPhotoId')
            ->never();

        $this->likeService
            ->shouldReceive('updateForWeeklyAggregation')
            ->never();

        $this->likeService
            ->shouldReceive('outputErrorLog')
            ->never()
            ->with('[いいね集計・週次]', "例外発生 対象：$photoId");

        $this->likeService
            ->shouldReceive('outputLog')
            ->never()
            ->with('[いいね集計・週次]', '週次いいね集計 END');

        $this->likeService->aggregateLikeWeekly();
    }

    public function providerAggregateLikeWeekly_isNotSunday(): array
    {
        return [
            'Monday' => [
                'date' => '2021-01-04',
                'dayOfWeek' => '月曜日'
            ],
            'Tuesday' => [
                'date' => '2021-01-05',
                'dayOfWeek' => '火曜日'
            ],
            'Wednesday' => [
                'date' => '2021-01-06',
                'dayOfWeek' => '水曜日'
            ],
            'Thursday' => [
                'date' => '2021-01-07',
                'dayOfWeek' => '木曜日'
            ],
            'Friday' => [
                'date' => '2021-01-08',
                'dayOfWeek' => '金曜日'
            ],
            'Saturday' => [
                'date' => '2021-01-09',
                'dayOfWeek' => '土曜日'
            ]
        ];
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function aggregateLikeWeekly_multiple()
    {
        $photoId1 = 'photo1';
        $likeAggregate1 = new LikeAggregate(['photo_id' => $photoId1, 'likes' => 10]);
        $photoId2 = 'photo2';
        $likeAggregate2 = new LikeAggregate(['photo_id' => $photoId2, 'likes' => 15]);
        $likeAggregate3 = new LikeAggregate(['photo_id' => $photoId1, 'likes' => 20]);
        $startAt = CarbonImmutable::parse('2021-01-03 00:00:01');
        $startOfLastWeek = Carbon::startOfLastWeek($startAt);
        $endOfLastWeek = Carbon::endOfLastWeek($startAt);
        $dayOfWeek = $startAt->isoFormat('dddd');

        $this->likeService->setCommandStartAt($startAt);

        $this->likeService
            ->shouldReceive('outputLog')
            ->never()
            ->with('[いいね集計・週次]', "本日 $dayOfWeek なのでスキップ");

        $this->likeAggregate
            ->shouldReceive('getForAggregation')
            ->once()
            ->with(Mockery::on(function ($actual) use ($startOfLastWeek) {
                $this->assertSame($startOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastWeek) {
                $this->assertSame($endOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'dailyType'))
            ->andReturn(new Collection([$likeAggregate1, $likeAggregate2, $likeAggregate3]));

        $this->likeService
            ->shouldReceive('outputLog')
            ->once()
            ->with('[いいね集計・週次]', '週次いいね集計 START');

        DB::shouldReceive('beginTransaction')->times(2);
        DB::shouldReceive('commit')->times(2);
        DB::shouldReceive('rollBack')->never();

        $this->likeService
            ->shouldReceive('registerForWeeklyAggregation')
            ->once()
            ->with(Mockery::on(function ($actual) use ($likeAggregate1, $likeAggregate3) {
                $this->assertSame($likeAggregate1->toArray(), $actual[0]->toArray());
                $this->assertSame($likeAggregate3->toArray(), $actual[1]->toArray());
                return true;
            }), Mockery::on(function ($actual) use ($startOfLastWeek) {
                $this->assertSame($startOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastWeek) {
                $this->assertSame($endOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }));
        $this->likeService
            ->shouldReceive('registerForWeeklyAggregation')
            ->once()
            ->with(Mockery::on(function ($actual) use ($likeAggregate2) {
                $this->assertSame($likeAggregate2->toArray(), $actual[0]->toArray());
                return true;
            }), Mockery::on(function ($actual) use ($startOfLastWeek) {
                $this->assertSame($startOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastWeek) {
                $this->assertSame($endOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }));

        $this->like
            ->shouldReceive('saveByPhotoId')
            ->once()
            ->with($photoId1, ['weekly_likes' => 30]);
        $this->like
            ->shouldReceive('saveByPhotoId')
            ->once()
            ->with($photoId2, ['weekly_likes' => 15]);

        $this->likeService
            ->shouldReceive('updateForWeeklyAggregation')
            ->once()
            ->with(Mockery::on(function ($actual) use ($likeAggregate1, $likeAggregate3) {
                $this->assertSame($likeAggregate1->toArray(), $actual[0]->toArray());
                $this->assertSame($likeAggregate3->toArray(), $actual[1]->toArray());
                return true;
            }), Mockery::on(function ($actual) use ($startOfLastWeek) {
                $this->assertSame($startOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastWeek) {
                $this->assertSame($endOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }));
        $this->likeService
            ->shouldReceive('updateForWeeklyAggregation')
            ->once()
            ->with(Mockery::on(function ($actual) use ($likeAggregate2) {
                $this->assertSame($likeAggregate2->toArray(), $actual[0]->toArray());
                return true;
            }), Mockery::on(function ($actual) use ($startOfLastWeek) {
                $this->assertSame($startOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastWeek) {
                $this->assertSame($endOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }));

        $this->likeService
            ->shouldReceive('outputErrorLog')
            ->never();

        $this->likeService
            ->shouldReceive('outputLog')
            ->once()
            ->with('[いいね集計・週次]', '週次いいね集計 END');

        $this->likeService->aggregateLikeWeekly();
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function aggregateLikeWeekly_single_exception()
    {
        $photoId = 'test_id';
        $likeAggregate = new LikeAggregate(['photo_id' => $photoId, 'likes' => 10]);
        $startAt = CarbonImmutable::parse('2021-01-03 00:00:01');
        $startOfLastWeek = Carbon::startOfLastWeek($startAt);
        $endOfLastWeek = Carbon::endOfLastWeek($startAt);
        $dayOfWeek = $startAt->isoFormat('dddd');
        $exception = new Exception('例外発生！');

        $this->likeService->setCommandStartAt($startAt);

        $this->likeService
            ->shouldReceive('outputLog')
            ->never()
            ->with('[いいね集計・週次]', "本日 $dayOfWeek なのでスキップ");

        $this->likeAggregate
            ->shouldReceive('getForAggregation')
            ->once()
            ->with(Mockery::on(function ($actual) use ($startOfLastWeek) {
                $this->assertSame($startOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastWeek) {
                $this->assertSame($endOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'dailyType'))
            ->andReturn(new Collection([$likeAggregate]));

        $this->likeService
            ->shouldReceive('outputLog')
            ->once()
            ->with('[いいね集計・週次]', '週次いいね集計 START');

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->never();
        DB::shouldReceive('rollBack')->once();

        $this->likeService
            ->shouldReceive('registerForWeeklyAggregation')
            ->once()
            ->with(Mockery::on(function ($actual) use ($likeAggregate) {
                $this->assertSame($likeAggregate->toArray(), $actual[0]->toArray());
                return true;
            }), Mockery::on(function ($actual) use ($startOfLastWeek) {
                $this->assertSame($startOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastWeek) {
                $this->assertSame($endOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }))->andThrow($exception);

        $this->like
            ->shouldReceive('saveByPhotoId')
            ->never()
            ->with($photoId, ['weekly_likes' => 10]);

        $this->likeService
            ->shouldReceive('updateForWeeklyAggregation')
            ->never()
            ->with($photoId, Mockery::on(function ($actual) use ($startOfLastWeek) {
                $this->assertSame($startOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastWeek) {
                $this->assertSame($endOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }));

        $this->likeService
            ->shouldReceive('outputErrorLog')
            ->once()
            ->with('[いいね集計・週次]', "例外発生 対象：$photoId");

        $this->likeService
            ->shouldReceive('outputLog')
            ->never()
            ->with('[いいね集計・週次]', '週次いいね集計 END');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('例外発生！');

        $this->likeService->aggregateLikeWeekly();
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function aggregateLikeWeekly_multiple_exception()
    {
        $photoId1 = 'photo1';
        $likeAggregate1 = new LikeAggregate(['photo_id' => $photoId1, 'likes' => 10]);
        $photoId2 = 'photo2';
        $likeAggregate2 = new LikeAggregate(['photo_id' => $photoId2, 'likes' => 15]);
        $photoId3 = 'photo3';
        $likeAggregate3 = new LikeAggregate(['photo_id' => $photoId3, 'likes' => 20]);
        $startAt = CarbonImmutable::parse('2021-01-03 00:00:01');
        $startOfLastWeek = Carbon::startOfLastWeek($startAt);
        $endOfLastWeek = Carbon::endOfLastWeek($startAt);
        $dayOfWeek = $startAt->isoFormat('dddd');
        $exception = new Exception('例外発生！');

        $this->likeService->setCommandStartAt($startAt);

        $this->likeService
            ->shouldReceive('outputLog')
            ->never()
            ->with('[いいね集計・週次]', "本日 $dayOfWeek なのでスキップ");

        $this->likeAggregate
            ->shouldReceive('getForAggregation')
            ->once()
            ->with(Mockery::on(function ($actual) use ($startOfLastWeek) {
                $this->assertSame($startOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastWeek) {
                $this->assertSame($endOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'dailyType'))
            ->andReturn(new Collection([$likeAggregate1, $likeAggregate2, $likeAggregate3]));

        $this->likeService
            ->shouldReceive('outputLog')
            ->once()
            ->with('[いいね集計・週次]', '週次いいね集計 START');

        DB::shouldReceive('beginTransaction')->times(2);
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollBack')->once();

        $this->likeService
            ->shouldReceive('registerForWeeklyAggregation')
            ->once()
            ->with(Mockery::on(function ($actual) use ($likeAggregate1) {
                $this->assertSame($likeAggregate1->toArray(), $actual[0]->toArray());
                return true;
            }), Mockery::on(function ($actual) use ($startOfLastWeek) {
                $this->assertSame($startOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastWeek) {
                $this->assertSame($endOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }));
        $this->likeService
            ->shouldReceive('registerForWeeklyAggregation')
            ->once()
            ->with(Mockery::on(function ($actual) use ($likeAggregate2) {
                $this->assertSame($likeAggregate2->toArray(), $actual[0]->toArray());
                return true;
            }), Mockery::on(function ($actual) use ($startOfLastWeek) {
                $this->assertSame($startOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastWeek) {
                $this->assertSame($endOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }))->andThrow($exception);
        $this->likeService
            ->shouldReceive('registerForWeeklyAggregation')
            ->never()
            ->with(Mockery::on(function ($actual) use ($likeAggregate3) {
                $this->assertSame($likeAggregate3->toArray(), $actual[0]->toArray());
                return true;
            }), Mockery::on(function ($actual) use ($startOfLastWeek) {
                $this->assertSame($startOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastWeek) {
                $this->assertSame($endOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }));

        $this->like
            ->shouldReceive('saveByPhotoId')
            ->once()
            ->with($photoId1, ['weekly_likes' => 10]);
        $this->like
            ->shouldReceive('saveByPhotoId')
            ->never()
            ->with($photoId2, ['weekly_likes' => 15]);
        $this->like
            ->shouldReceive('saveByPhotoId')
            ->never()
            ->with($photoId2, ['weekly_likes' => 20]);

        $this->likeService
            ->shouldReceive('updateForWeeklyAggregation')
            ->once()
            ->with(Mockery::on(function ($actual) use ($likeAggregate1) {
                $this->assertSame($likeAggregate1->toArray(), $actual[0]->toArray());
                return true;
            }), Mockery::on(function ($actual) use ($startOfLastWeek) {
                $this->assertSame($startOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastWeek) {
                $this->assertSame($endOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }));
        $this->likeService
            ->shouldReceive('updateForWeeklyAggregation')
            ->never()
            ->with(Mockery::on(function ($actual) use ($likeAggregate2) {
                $this->assertSame($likeAggregate2->toArray(), $actual[0]->toArray());
                return true;
            }), Mockery::on(function ($actual) use ($startOfLastWeek) {
                $this->assertSame($startOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastWeek) {
                $this->assertSame($endOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }));
        $this->likeService
            ->shouldReceive('updateForWeeklyAggregation')
            ->never()
            ->with(Mockery::on(function ($actual) use ($likeAggregate3) {
                $this->assertSame($likeAggregate3->toArray(), $actual[0]->toArray());
                return true;
            }), Mockery::on(function ($actual) use ($startOfLastWeek) {
                $this->assertSame($startOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastWeek) {
                $this->assertSame($endOfLastWeek->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }));

        $this->likeService
            ->shouldReceive('outputErrorLog')
            ->once()
            ->with('[いいね集計・週次]', "例外発生 対象：$photoId2");

        $this->likeService
            ->shouldReceive('outputLog')
            ->never()
            ->with('[いいね集計・週次]', '週次いいね集計 END');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('例外発生！');

        $this->likeService->aggregateLikeWeekly();
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function aggregateLikeMonthly_single()
    {
        $photoId = 'test_id';
        $likeAggregate = new LikeAggregate(['photo_id' => $photoId, 'likes' => 15]);
        $startAt = CarbonImmutable::parse('2021-01-01 00:00:01');
        $day = $startAt->day;
        $startOfLastMonth = Carbon::startOfLastMonth($startAt);
        $endOfLastMonth = Carbon::endOfLastMonth($startAt);

        $this->likeService->setCommandStartAt($startAt);

        $this->likeService
            ->shouldReceive('outputLog')
            ->never()
            ->with('[いいね集計・月次]', "本日 $day 日なのでスキップ");

        $this->likeService
            ->shouldReceive('outputLog')
            ->once()
            ->with('[いいね集計・月次]', '月次いいね集計 START');

        $this->likeAggregate
            ->shouldReceive('getForAggregation')
            ->once()
            ->with(Mockery::on(function ($actual) use ($startOfLastMonth) {
                $this->assertSame($startOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastMonth) {
                $this->assertSame($endOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'weeklyType'))
            ->andReturn(new Collection([$likeAggregate]));

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollBack')->never();

        $this->likeAggregate
            ->shouldReceive('registerForAggregation')
            ->once()
            ->with($likeAggregate, Mockery::on(function ($actual) use ($startOfLastMonth) {
                $this->assertSame($startOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastMonth) {
                $this->assertSame($endOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'monthlyType'));

        $this->like
            ->shouldReceive('saveByPhotoId')
            ->once()
            ->with($photoId, ['month_likes' => 15]);

        $this->likeAggregate
            ->shouldReceive('updateForAggregation')
            ->once()
            ->with($photoId, Mockery::on(function ($actual) use ($startOfLastMonth) {
                $this->assertSame($startOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastMonth) {
                $this->assertSame($endOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'weeklyType'),
                ['status' => config('const.PHOTO_AGGREGATION.STATUS.COMPLETE')]);

        $this->likeService
            ->shouldReceive('outputErrorLog')
            ->never()
            ->with('[いいね集計・月次]', "例外発生　対象：$photoId");

        $this->likeService
            ->shouldReceive('outputLog')
            ->once()
            ->with('[いいね集計・月次]', '月次いいね集計 END');

        $this->likeService->aggregateLikeMonthly();
    }

    /**
     * @test
     * @dataProvider providerAggregateLikeMonthly_isNotFirstDay
     */
    public function aggregateLikeMonthly_isNotFirstDay($carbon)
    {
        $photoId = 'test_id';
        $startAt = CarbonImmutable::parse($carbon);
        $day = $startAt->day;

        $this->likeService->setCommandStartAt($startAt);

        $this->likeService
            ->shouldReceive('outputLog')
            ->once()
            ->with('[いいね集計・月次]', "本日 $day 日なのでスキップ");

        $this->likeService
            ->shouldReceive('outputLog')
            ->never()
            ->with('[いいね集計・月次]', '月次いいね集計 START');

        $this->likeAggregate
            ->shouldReceive('getForAggregation')
            ->never();

        DB::shouldReceive('beginTransaction')->never();
        DB::shouldReceive('commit')->never();
        DB::shouldReceive('rollBack')->never();

        $this->likeAggregate
            ->shouldReceive('registerForAggregation')
            ->never();

        $this->like
            ->shouldReceive('saveByPhotoId')
            ->never();

        $this->likeAggregate
            ->shouldReceive('updateForAggregation')
            ->never();

        $this->likeService
            ->shouldReceive('outputErrorLog')
            ->never()
            ->with('[いいね集計・月次]', "例外発生　対象：$photoId");

        $this->likeService
            ->shouldReceive('outputLog')
            ->never()
            ->with('[いいね集計・月次]', '月次いいね集計 END');

        $this->likeService->aggregateLikeMonthly();
    }

    public function providerAggregateLikeMonthly_isNotFirstDay(): array
    {
        return [
            '2日' => [
                'carbon' => '2021-01-02'
            ],
            '3日' => [
                'carbon' => '2021-01-03'
            ],
            '4日' => [
                'carbon' => '2021-01-04'
            ],
            '5日' => [
                'carbon' => '2021-01-05'
            ],
            '6日' => [
                'carbon' => '2021-01-06'
            ],
            '7日' => [
                'carbon' => '2021-01-07'
            ],
            '8日' => [
                'carbon' => '2021-01-08'
            ],
            '9日' => [
                'carbon' => '2021-01-09'
            ],
            '10日' => [
                'carbon' => '2021-01-10'
            ],
            '11日' => [
                'carbon' => '2021-01-11'
            ],
            '12日' => [
                'carbon' => '2021-01-12'
            ],
            '13日' => [
                'carbon' => '2021-01-13'
            ],
            '14日' => [
                'carbon' => '2021-01-14'
            ],
            '15日' => [
                'carbon' => '2021-01-15'
            ],
            '16日' => [
                'carbon' => '2021-01-16'
            ],
            '17日' => [
                'carbon' => '2021-01-17'
            ],
            '18日' => [
                'carbon' => '2021-01-18'
            ],
            '19日' => [
                'carbon' => '2021-01-19'
            ],
            '20日' => [
                'carbon' => '2021-01-20'
            ],
            '21日' => [
                'carbon' => '2021-01-21'
            ],
            '22日' => [
                'carbon' => '2021-01-22'
            ],
            '23日' => [
                'carbon' => '2021-01-23'
            ],
            '24日' => [
                'carbon' => '2021-01-24'
            ],
            '25日' => [
                'carbon' => '2021-01-25'
            ],
            '26日' => [
                'carbon' => '2021-01-26'
            ],
            '27日' => [
                'carbon' => '2021-01-27'
            ],
            '28日' => [
                'carbon' => '2021-01-18'
            ],
            '29日' => [
                'carbon' => '2021-01-29'
            ],
            '30日' => [
                'carbon' => '2021-01-30'
            ],
            '31日' => [
                'carbon' => '2021-01-31'
            ],
        ];
    }

    /**
     * @test
     */
    public function aggregateLikeMonthly_multiple()
    {
        $photo1 = 'test_id1';
        $likeAggregate1 = new LikeAggregate(['photo_id' => $photo1, 'likes' => 5]);
        $photo2 = 'test_id2';
        $likeAggregate2 = new LikeAggregate(['photo_id' => $photo2, 'likes' => 10]);
        $photo3 = 'test_id3';
        $likeAggregate3 = new LikeAggregate(['photo_id' => $photo3, 'likes' => 15]);
        $startAt = CarbonImmutable::parse('2021-01-01 00:00:01');
        $day = $startAt->day;
        $startOfLastMonth = Carbon::startOfLastMonth($startAt);
        $endOfLastMonth = Carbon::endOfLastMonth($startAt);

        $this->likeService->setCommandStartAt($startAt);

        $this->likeService
            ->shouldReceive('outputLog')
            ->never()
            ->with('[いいね集計・月次]', "本日 $day 日なのでスキップ");

        $this->likeService
            ->shouldReceive('outputLog')
            ->once()
            ->with('[いいね集計・月次]', '月次いいね集計 START');

        $this->likeAggregate
            ->shouldReceive('getForAggregation')
            ->once()
            ->with(Mockery::on(function ($actual) use ($startOfLastMonth) {
                $this->assertSame($startOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastMonth) {
                $this->assertSame($endOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'weeklyType'))
            ->andReturn(new Collection([$likeAggregate1, $likeAggregate2, $likeAggregate3]));

        DB::shouldReceive('beginTransaction')->times(3);
        DB::shouldReceive('commit')->times(3);
        DB::shouldReceive('rollBack')->never();

        $this->likeAggregate
            ->shouldReceive('registerForAggregation')
            ->once()
            ->with($likeAggregate1, Mockery::on(function ($actual) use ($startOfLastMonth) {
                $this->assertSame($startOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastMonth) {
                $this->assertSame($endOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'monthlyType'));
        $this->likeAggregate
            ->shouldReceive('registerForAggregation')
            ->once()
            ->with($likeAggregate2, Mockery::on(function ($actual) use ($startOfLastMonth) {
                $this->assertSame($startOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastMonth) {
                $this->assertSame($endOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'monthlyType'));
        $this->likeAggregate
            ->shouldReceive('registerForAggregation')
            ->once()
            ->with($likeAggregate3, Mockery::on(function ($actual) use ($startOfLastMonth) {
                $this->assertSame($startOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastMonth) {
                $this->assertSame($endOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'monthlyType'));

        $this->like
            ->shouldReceive('saveByPhotoId')
            ->once()
            ->with($photo1, ['month_likes' => 5]);
        $this->like
            ->shouldReceive('saveByPhotoId')
            ->once()
            ->with($photo2, ['month_likes' => 10]);
        $this->like
            ->shouldReceive('saveByPhotoId')
            ->once()
            ->with($photo3, ['month_likes' => 15]);

        $this->likeAggregate
            ->shouldReceive('updateForAggregation')
            ->once()
            ->with($photo1, Mockery::on(function ($actual) use ($startOfLastMonth) {
                $this->assertSame($startOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastMonth) {
                $this->assertSame($endOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'weeklyType'),
                ['status' => config('const.PHOTO_AGGREGATION.STATUS.COMPLETE')]);
        $this->likeAggregate
            ->shouldReceive('updateForAggregation')
            ->once()
            ->with($photo2, Mockery::on(function ($actual) use ($startOfLastMonth) {
                $this->assertSame($startOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastMonth) {
                $this->assertSame($endOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'weeklyType'),
                ['status' => config('const.PHOTO_AGGREGATION.STATUS.COMPLETE')]);
        $this->likeAggregate
            ->shouldReceive('updateForAggregation')
            ->once()
            ->with($photo3, Mockery::on(function ($actual) use ($startOfLastMonth) {
                $this->assertSame($startOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastMonth) {
                $this->assertSame($endOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'weeklyType'),
                ['status' => config('const.PHOTO_AGGREGATION.STATUS.COMPLETE')]);

        $this->likeService
            ->shouldReceive('outputErrorLog')
            ->never();

        $this->likeService
            ->shouldReceive('outputLog')
            ->once()
            ->with('[いいね集計・月次]', '月次いいね集計 END');

        $this->likeService->aggregateLikeMonthly();
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function aggregateLikeMonthly_single_exception()
    {
        $photoId = 'test_id';
        $likeAggregate = new LikeAggregate(['photo_id' => $photoId, 'likes' => 15]);
        $startAt = CarbonImmutable::parse('2021-01-01 00:00:01');
        $day = $startAt->day;
        $startOfLastMonth = Carbon::startOfLastMonth($startAt);
        $endOfLastMonth = Carbon::endOfLastMonth($startAt);
        $exception = new Exception('例外発生！');

        $this->likeService->setCommandStartAt($startAt);

        $this->likeService
            ->shouldReceive('outputLog')
            ->never()
            ->with('[いいね集計・月次]', "本日 $day 日なのでスキップ");

        $this->likeService
            ->shouldReceive('outputLog')
            ->once()
            ->with('[いいね集計・月次]', '月次いいね集計 START');

        $this->likeAggregate
            ->shouldReceive('getForAggregation')
            ->once()
            ->with(Mockery::on(function ($actual) use ($startOfLastMonth) {
                $this->assertSame($startOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastMonth) {
                $this->assertSame($endOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'weeklyType'))
            ->andReturn(new Collection([$likeAggregate]));

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->never();
        DB::shouldReceive('rollBack')->once();

        $this->likeAggregate
            ->shouldReceive('registerForAggregation')
            ->once()
            ->with($likeAggregate, Mockery::on(function ($actual) use ($startOfLastMonth) {
                $this->assertSame($startOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastMonth) {
                $this->assertSame($endOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'monthlyType'))
            ->andThrow($exception);

        $this->like
            ->shouldReceive('saveByPhotoId')
            ->never()
            ->with($photoId, ['month_likes' => 15]);

        $this->likeAggregate
            ->shouldReceive('updateForAggregation')
            ->never()
            ->with($photoId, Mockery::on(function ($actual) use ($startOfLastMonth) {
                $this->assertSame($startOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastMonth) {
                $this->assertSame($endOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'weeklyType'),
                ['status' => config('const.PHOTO_AGGREGATION.STATUS.COMPLETE')]);

        $this->likeService
            ->shouldReceive('outputErrorLog')
            ->once()
            ->with('[いいね集計・月次]', "例外発生　対象：$photoId");

        $this->likeService
            ->shouldReceive('outputLog')
            ->never()
            ->with('[いいね集計・月次]', '月次いいね集計 END');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('例外発生！');

        $this->likeService->aggregateLikeMonthly();
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function aggregateLikeMonthly_multiple_exception()
    {
        $photo1 = 'test_id1';
        $likeAggregate1 = new LikeAggregate(['photo_id' => $photo1, 'likes' => 5]);
        $photo2 = 'test_id2';
        $likeAggregate2 = new LikeAggregate(['photo_id' => $photo2, 'likes' => 10]);
        $photo3 = 'test_id3';
        $likeAggregate3 = new LikeAggregate(['photo_id' => $photo3, 'likes' => 15]);
        $startAt = CarbonImmutable::parse('2021-01-01 00:00:01');
        $day = $startAt->day;
        $startOfLastMonth = Carbon::startOfLastMonth($startAt);
        $endOfLastMonth = Carbon::endOfLastMonth($startAt);
        $exception = new Exception('例外発生！');

        $this->likeService->setCommandStartAt($startAt);

        $this->likeService
            ->shouldReceive('outputLog')
            ->never()
            ->with('[いいね集計・月次]', "本日 $day 日なのでスキップ");

        $this->likeService
            ->shouldReceive('outputLog')
            ->once()
            ->with('[いいね集計・月次]', '月次いいね集計 START');

        $this->likeAggregate
            ->shouldReceive('getForAggregation')
            ->once()
            ->with(Mockery::on(function ($actual) use ($startOfLastMonth) {
                $this->assertSame($startOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastMonth) {
                $this->assertSame($endOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'weeklyType'))
            ->andReturn(new Collection([$likeAggregate1, $likeAggregate2, $likeAggregate3]));

        DB::shouldReceive('beginTransaction')->times(2);
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollBack')->once();

        $this->likeAggregate
            ->shouldReceive('registerForAggregation')
            ->once()
            ->with($likeAggregate1, Mockery::on(function ($actual) use ($startOfLastMonth) {
                $this->assertSame($startOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastMonth) {
                $this->assertSame($endOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'monthlyType'));
        $this->likeAggregate
            ->shouldReceive('registerForAggregation')
            ->once()
            ->with($likeAggregate2, Mockery::on(function ($actual) use ($startOfLastMonth) {
                $this->assertSame($startOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastMonth) {
                $this->assertSame($endOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'monthlyType'))
            ->andThrow($exception);
        $this->likeAggregate
            ->shouldReceive('registerForAggregation')
            ->never()
            ->with($likeAggregate3, Mockery::on(function ($actual) use ($startOfLastMonth) {
                $this->assertSame($startOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastMonth) {
                $this->assertSame($endOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'monthlyType'));

        $this->like
            ->shouldReceive('saveByPhotoId')
            ->once()
            ->with($photo1, ['month_likes' => 5]);
        $this->like
            ->shouldReceive('saveByPhotoId')
            ->never()
            ->with($photo2, ['month_likes' => 10]);
        $this->like
            ->shouldReceive('saveByPhotoId')
            ->never()
            ->with($photo3, ['month_likes' => 15]);

        $this->likeAggregate
            ->shouldReceive('updateForAggregation')
            ->once()
            ->with($photo1, Mockery::on(function ($actual) use ($startOfLastMonth) {
                $this->assertSame($startOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastMonth) {
                $this->assertSame($endOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'weeklyType'),
                ['status' => config('const.PHOTO_AGGREGATION.STATUS.COMPLETE')]);
        $this->likeAggregate
            ->shouldReceive('updateForAggregation')
            ->never()
            ->with($photo2, Mockery::on(function ($actual) use ($startOfLastMonth) {
                $this->assertSame($startOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastMonth) {
                $this->assertSame($endOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'weeklyType'),
                ['status' => config('const.PHOTO_AGGREGATION.STATUS.COMPLETE')]);
        $this->likeAggregate
            ->shouldReceive('updateForAggregation')
            ->never()
            ->with($photo3, Mockery::on(function ($actual) use ($startOfLastMonth) {
                $this->assertSame($startOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endOfLastMonth) {
                $this->assertSame($endOfLastMonth->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'weeklyType'),
                ['status' => config('const.PHOTO_AGGREGATION.STATUS.COMPLETE')]);

        $this->likeService
            ->shouldReceive('outputErrorLog')
            ->once()
            ->with('[いいね集計・月次]', "例外発生　対象：$photo2");

        $this->likeService
            ->shouldReceive('outputLog')
            ->never()
            ->with('[いいね集計・月次]', '月次いいね集計 END');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('例外発生！');

        $this->likeService->aggregateLikeMonthly();
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function registerForWeeklyAggregation()
    {
        $photo1 = 'not_carry_over';
        $likeAggregate1 = new LikeAggregate(['photo_id' => $photo1, 'likes' => 5]);
        $photo2 = 'carry_over';
        $likeAggregate2 = new LikeAggregate(['photo_id' => $photo2, 'likes' => 10]);
        $likeAggregate2->setAttribute('carry_over', 1);
        $likeAggregate3 = new LikeAggregate(['photo_id' => $photo2, 'likes' => 15]);
        $likeAggregate3->setAttribute('carry_over', 2);

        $collection = new Collection([
            $likeAggregate1,
            $likeAggregate2,
            $likeAggregate3,
            $likeAggregate2,
            $likeAggregate3,
        ]);

        $startAt = CarbonImmutable::parse('2021-01-29');
        $endAt = CarbonImmutable::parse('2021-02-04');

        $this->likeAggregate
            ->shouldReceive('registerForAggregation')
            ->once()
            ->with($likeAggregate1, Mockery::on(function ($actual) use ($startAt) {
                $this->assertSame($startAt->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endAt) {
                $this->assertSame($endAt->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'weeklyType'));
        $this->likeAggregate
            ->shouldReceive('registerForAggregation')
            ->twice()
            ->with($likeAggregate2, Mockery::on(function ($actual) use ($startAt) {
                $this->assertSame($startAt->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($startAt) {
                $this->assertSame($startAt->endOfMonth()->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'weeklyType'));
        $this->likeAggregate
            ->shouldReceive('registerForAggregation')
            ->twice()
            ->with($likeAggregate3, Mockery::on(function ($actual) use ($endAt) {
                $this->assertSame($endAt->startOfMonth()->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), Mockery::on(function ($actual) use ($endAt) {
                $this->assertSame($endAt->toDateTimeString(), $actual->toDateTimeString());
                return true;
            }), $this->getPrivatePropertyForMockObject($this->likeService, 'weeklyType'));

        $this->likeService->registerForWeeklyAggregation($collection, $startAt, $endAt);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function updateForWeeklyAggregation()
    {
        $photo1 = 'not_set_carry_over';
        $likeAggregate1 = new LikeAggregate(['photo_id' => $photo1, 'likes' => 5]);
        $photo2 = 'carry_over_1';
        $likeAggregate2 = new LikeAggregate(['photo_id' => $photo2, 'likes' => 10]);
        $likeAggregate2->setAttribute('carry_over', 1);
        $likeAggregate3 = new LikeAggregate(['photo_id' => $photo2, 'likes' => 15]);
        $likeAggregate3->setAttribute('carry_over', 2);
        $photo3 = 'carry_over_2';
        $likeAggregate4 = new LikeAggregate(['photo_id' => $photo3, 'likes' => 20]);
        $likeAggregate4->setAttribute('carry_over', 1);
        $likeAggregate5 = new LikeAggregate(['photo_id' => $photo3, 'likes' => 25]);
        $likeAggregate5->setAttribute('carry_over', 2);

        $collection = new Collection([
            $likeAggregate1,
            $likeAggregate2,
            $likeAggregate3,
            $likeAggregate4,
            $likeAggregate5
        ]);

        $startAt = CarbonImmutable::parse('2021-01-29');
        $endAt = CarbonImmutable::parse('2021-02-04');

        $this->likeAggregate
            ->shouldReceive('updateForAggregation')
            ->once()
            ->with(
                $photo1,
                Mockery::on(function ($actual) use ($startAt) {
                    $this->assertSame($startAt->toDateTimeString(), $actual->toDateTimeString());
                    return true;
                }),
                Mockery::on(function ($actual) use ($endAt) {
                    $this->assertSame($endAt->toDateTimeString(), $actual->toDateTimeString());
                    return true;
                }),
                $this->getPrivatePropertyForMockObject($this->likeService, 'dailyType'),
                ['status' => config('const.PHOTO_AGGREGATION.STATUS.COMPLETE')]
            );
        $this->likeAggregate
            ->shouldReceive('updateForAggregation')
            ->once()
            ->with(
                $photo2,
                Mockery::on(function ($actual) use ($startAt) {
                    $this->assertSame($startAt->toDateTimeString(), $actual->toDateTimeString());
                    return true;
                }),
                Mockery::on(function ($actual) use ($startAt) {
                    $this->assertSame($startAt->endOfMonth()->toDateTimeString(), $actual->toDateTimeString());
                    return true;
                }),
                $this->getPrivatePropertyForMockObject($this->likeService, 'dailyType'),
                ['status' => config('const.PHOTO_AGGREGATION.STATUS.COMPLETE')]
            );
        $this->likeAggregate
            ->shouldReceive('updateForAggregation')
            ->once()
            ->with(
                $photo2,
                Mockery::on(function ($actual) use ($endAt) {
                    $this->assertSame($endAt->startOfMonth()->toDateTimeString(), $actual->toDateTimeString());
                    return true;
                }),
                Mockery::on(function ($actual) use ($endAt) {
                    $this->assertSame($endAt->toDateTimeString(), $actual->toDateTimeString());
                    return true;
                }),
                $this->getPrivatePropertyForMockObject($this->likeService, 'dailyType'),
                ['status' => config('const.PHOTO_AGGREGATION.STATUS.COMPLETE')]
            );
        $this->likeAggregate
            ->shouldReceive('updateForAggregation')
            ->once()
            ->with(
                $photo3,
                Mockery::on(function ($actual) use ($startAt) {
                    $this->assertSame($startAt->toDateTimeString(), $actual->toDateTimeString());
                    return true;
                }),
                Mockery::on(function ($actual) use ($startAt) {
                    $this->assertSame($startAt->endOfMonth()->toDateTimeString(), $actual->toDateTimeString());
                    return true;
                }),
                $this->getPrivatePropertyForMockObject($this->likeService, 'dailyType'),
                ['status' => config('const.PHOTO_AGGREGATION.STATUS.COMPLETE')]
            );
        $this->likeAggregate
            ->shouldReceive('updateForAggregation')
            ->once()
            ->with(
                $photo3,
                Mockery::on(function ($actual) use ($endAt) {
                    $this->assertSame($endAt->startOfMonth()->toDateTimeString(), $actual->toDateTimeString());
                    return true;
                }),
                Mockery::on(function ($actual) use ($endAt) {
                    $this->assertSame($endAt->toDateTimeString(), $actual->toDateTimeString());
                    return true;
                }),
                $this->getPrivatePropertyForMockObject($this->likeService, 'dailyType'),
                ['status' => config('const.PHOTO_AGGREGATION.STATUS.COMPLETE')]
            );

        $this->likeService->updateForWeeklyAggregation($collection, $startAt, $endAt);
    }

    /**
     * @test
     */
    public function sendThrowableMail()
    {
        Mail::fake();

        $this->likeService->sendThrowableMail('例外発生のお知らせ', '例外発生しました。');

        Mail::assertSent(ThrowableMail::class, function ($mail){
            return $mail->hasTo('wadakatukoyo330@gmail.com');
        });

        Mail::assertSent(ThrowableMail::class, 1);
    }
}
