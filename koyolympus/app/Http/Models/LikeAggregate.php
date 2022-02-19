<?php
declare(strict_types=1);

namespace App\Http\Models;

use DB;
use Exception;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

class LikeAggregate extends Model
{
    protected $guarded = ['id'];

    public function getForWeeklyAggregation(CarbonImmutable $startAt, CarbonImmutable $endAt)
    {
        $dailyType = config('const.PHOTO_AGGREGATION.TYPE.DAILY');
        return self::query()
            ->join('likes', 'likes.photo_id', '=', 'like_aggregates.photo_id')
            ->where('aggregate_type', $dailyType)
            ->whereBetween('start_at', [$startAt, $endAt])
            ->whereBetween('end_at', [$startAt, $endAt])
            ->select([
                'likes.id',
                'like_aggregates.photo_id',
                DB::raw('CAST(sum(like_aggregates.likes) AS SIGNED) as likes')
            ])
            ->groupBy('likes.id', 'like_aggregates.photo_id')
            ->get();
    }

    public function registerAggregatedLike(array $likeInfo, CarbonImmutable $startAt, CarbonImmutable $endAt, int $type)
    {
        self::query()->create([
            'photo_id' => $likeInfo['photo_id'],
            'aggregate_type' => $type,
            'likes' => $likeInfo['likes'],
            'start_at' => $startAt,
            'end_at' => $endAt
        ]);
    }

    /**
     * @throws Exception
     */
    public function deleteByPhotoIdAndPeriod(
        string $photoId,
        CarbonImmutable $startAt,
        CarbonImmutable $endAt,
        int $type
    ) {
        self::query()
            ->where('photo_id', $photoId)
            ->where('aggregate_type', $type)
            ->whereDate('start_at', $startAt)
            ->whereDate('end_at', $endAt)
            ->delete();
    }
}
