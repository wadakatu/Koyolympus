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

    public function __construct(Like $like, LikeAggregate $likeAggregate)
    {
        $this->like = $like;
        $this->likeAggregate = $likeAggregate;
    }


    public function setCommandStartAt(): void
    {
        $this->startAt = CarbonImmutable::now();
    }

    /**
     * @throws Exception
     */
    public function aggregateLikeDaily(): void
    {
        $targetRecords = $this->like->getForDailyAggregation();
        $type = config('const.PHOTO_AGGREGATION.TYPE.DAILY');

        if ($targetRecords->isEmpty()) {
            Log::info('[いいね集計・日次] 集計対象０件のためスキップ');
            return;
        }

        Log::info('[いいね集計・日次] 日次いいね集計 START');
        DB::beginTransaction();
        foreach ($targetRecords->toArray() as $record) {
            $photoId = $record['photo_id'];
            try {
                $this->likeAggregate->deleteByPhotoIdAndPeriod($photoId, $this->startAt, $this->startAt, $type);
                $this->likeAggregate->registerAggregatedLike($record, $this->startAt, $this->startAt, $type);
                $this->like->find($record['id'])->fill(['likes' => 0])->save();
            } catch (Exception $e) {
                DB::rollBack();
                Log::error("[いいね集計・日次] 例外発生　対象：$photoId]");
                throw $e;
            }
        }

        DB::commit();
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
        $type = config('const.PHOTO_AGGREGATION.TYPE.WEEKLY');

        $targetRecords = $this->likeAggregate->getForWeeklyAggregation($startOfLastWeek, $endOfLastWeek);

        Log::info('[いいね集計・週次] 日次いいね集計 START');
        DB::beginTransaction();
        foreach ($targetRecords->toArray() as $record) {
            $photoId = $record['photo_id'];
            try {
                $this->likeAggregate->deleteByPhotoIdAndPeriod($photoId, $startOfLastWeek, $endOfLastWeek, $type);
                $this->likeAggregate->registerAggregatedLike($record, $startOfLastWeek, $endOfLastWeek, $type);
                $this->like->find($record['id'])->fill(['week_likes' => $record['likes']])->save();
            } catch (Exception $e) {
                DB::rollBack();
                Log::error("[いいね集計・週次] 例外発生　対象：$photoId]");
                throw $e;
            }
        }

        DB::commit();
        Log::info('[いいね集計・週次] 日次いいね集計 END');
    }
}
