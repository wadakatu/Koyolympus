<?php
declare(strict_types=1);

namespace App\Http\Services;

use DB;
use Log;
use Exception;
use Carbon\Carbon;
use App\Http\Models\Like;
use Carbon\CarbonImmutable;
use App\Http\Models\LikeAggregate;

class LikeService
{
    private $like;

    private $likeAggregate;

    /* @var CarbonImmutable */
    private $startAt;

    /* @var int */
    private $dailyType;

    /* @var int */
    private $weeklyType;

    /* @var int */
    private $monthlyType;

    public function __construct(Like $like, LikeAggregate $likeAggregate)
    {
        $this->like = $like;
        $this->likeAggregate = $likeAggregate;

        $this->dailyType = config('const.PHOTO_AGGREGATION.TYPE.DAILY');
        $this->weeklyType = config('const.PHOTO_AGGREGATION.TYPE.WEEKLY');
        $this->monthlyType = config('const.PHOTO_AGGREGATION.TYPE.MONTHLY');
    }


    public function setCommandStartAt(CarbonImmutable $carbon): void
    {
        $this->startAt = $carbon;
    }

    /**
     * @throws Exception
     */
    public function aggregateLikeDaily(): void
    {
        $targetRecords = $this->like->getForDailyAggregation();

        if ($targetRecords->isEmpty()) {
            Log::info('[いいね集計・日次] 集計対象０件のためスキップ');
            return;
        }

        Log::info('[いいね集計・日次] 日次いいね集計 START');
        DB::beginTransaction();
        foreach ($targetRecords->toArray() as $record) {
            $photoId = $record['photo_id'];
            try {
                $this->likeAggregate->registerAggregatedLike($record, $this->startAt, $this->startAt, $this->dailyType);
                $this->like->saveById($record['id'], ['likes' => 0]);
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                Log::error("[いいね集計・日次] 例外発生　対象：$photoId]");
                throw $e;
            }
        }

        Log::info('[いいね集計・日次] 日次いいね集計 END');
    }

    /**
     * @throws Exception
     */
    public function aggregateLikeWeekly(): void
    {
        if (!$this->startAt->isSunday()) {
            Carbon::setLocale('ja');
            $dayOfWeek = $this->startAt->isoFormat('dddd');
            Log::info("[いいね集計・週次] 本日 $dayOfWeek なのでスキップ");
            return;
        }

        $startOfLastWeek = Carbon::startOfLastWeek($this->startAt);
        $endOfLastWeek = Carbon::endOfLastWeek($this->startAt);

        $targetRecords = $this->likeAggregate->getForAggregation($startOfLastWeek, $endOfLastWeek, $this->dailyType);

        Log::info('[いいね集計・週次] 週次いいね集計 START');
        DB::beginTransaction();
        foreach ($targetRecords->toArray() as $record) {
            $photoId = $record['photo_id'];
            try {
                $this->likeAggregate->registerAggregatedLike($record, $startOfLastWeek, $endOfLastWeek,
                    $this->weeklyType);
                $this->like->find($record['id'], ['week_likes' => $record['likes']]);
                $this->likeAggregate->saveById(
                    $record['like_aggregate_id'],
                    ['status' => config('const.PHOTO_AGGREGATION.STATUS.COMPLETE')]
                );
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                Log::error("[いいね集計・週次] 例外発生　対象：$photoId]");
                throw $e;
            }
        }

        Log::info('[いいね集計・週次] 週次いいね集計 END');
    }

    /**
     * @throws Exception
     */
    public function aggregateLikeMonthly(): void
    {
        if (!Carbon::isFirstDayOfMonth($this->startAt)) {
            $day = $this->startAt->day;
            Log::info("[いいね集計・月次] 本日 $day 日なのでスキップ");
            return;
        }

        $startOfLastMonth = Carbon::startOfLastMonth($this->startAt);
        $endOfLastMonth = Carbon::endOfLastMonth($this->startAt);

        $targetRecords = $this->likeAggregate->getForAggregation($startOfLastMonth, $endOfLastMonth, $this->weeklyType);

        Log::info('[いいね集計・月次] 月次いいね集計 START');
        DB::beginTransaction();
        foreach ($targetRecords->toArray() as $record) {
            $photoId = $record['photo_id'];
            try {
                $this->likeAggregate->registerAggregatedLike($record, $startOfLastMonth, $endOfLastMonth,
                    $this->monthlyType);
                $this->like->saveById($record['id'], ['month_likes' => $record['likes']]);
                $this->likeAggregate->saveById(
                    $record['like_aggregate_id'],
                    ['status' => config('const.PHOTO_AGGREGATION.STATUS.COMPLETE')]
                );
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                Log::error("[いいね集計・月次] 例外発生　対象：$photoId]");
                throw $e;
            }
        }

        Log::info('[いいね集計・月次] 月次いいね集計 END');
    }
}
