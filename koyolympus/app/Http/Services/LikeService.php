<?php
declare(strict_types=1);

namespace App\Http\Services;

use DB;
use Log;
use Exception;
use Carbon\Carbon;
use App\Http\Models\Like;
use App\Http\Models\LikeAggregate;

class LikeService
{
    private $like;

    private $likeAggregate;

    private $startAt;

    public function __construct(Like $like, LikeAggregate $likeAggregate)
    {
        $this->like = $like;
        $this->likeAggregate = $likeAggregate;
    }

    /**
     * @throws Exception
     */
    public function aggregateLikeDaily()
    {
        $targetRecords = $this->like->getForDailyAggregation();
        $type = config('const.PHOTO_AGGREGATION.TYPE.DAILY');

        Log::info('[いいね集計バッチ] 日毎いいね集計 START');
        DB::beginTransaction();
        foreach ($targetRecords as $record) {
            try {
                $this->likeAggregate->registerAggregatedLike($record, $this->startAt, $this->startAt, $type);
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                Log::error("[いいね集計バッチ] 例外発生　対象：$record->photo_id");
                throw $e;
            }
        }
        Log::info('[いいね集計バッチ] 日毎いいね集計 END');
    }

    public function setCommandStartAt()
    {
        $this->startAt = Carbon::now();
    }
}
