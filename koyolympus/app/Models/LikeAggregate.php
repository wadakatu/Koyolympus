<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\DateFormat;
use Carbon\CarbonImmutable;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LikeAggregate extends Model
{
    use DateFormat;
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * いいね数集計用スコープ
     *
     * @param  Builder<LikeAggregate>  $query
     * @param  CarbonImmutable  $startAt
     * @param  CarbonImmutable  $endAt
     * @param  int  $type
     * @return Builder<LikeAggregate>
     */
    public function scopeForAggregation(
        Builder $query,
        CarbonImmutable $startAt,
        CarbonImmutable $endAt,
        int $type
    ): Builder {
        return $query
            ->where('status', config('const.PHOTO_AGGREGATION.STATUS.INCOMPLETE'))
            ->where('aggregate_type', $type)
            ->whereDate('start_at', '>=', $startAt)
            ->whereDate('end_at', '<=', $endAt);
    }

    /**
     * 日次かつ集計期間が月跨ぎの場合に適用されるスコープ
     *
     * @param  Builder<LikeAggregate>  $query
     * @param  CarbonImmutable  $startAt
     * @param  CarbonImmutable  $endAt
     * @param  int  $type
     * @return Builder<LikeAggregate>
     */
    public function scopeAddSelectWhenDailyAndDiffMonth(
        Builder $query,
        CarbonImmutable $startAt,
        CarbonImmutable $endAt,
        int $type
    ): Builder {
        if ($type === config('const.PHOTO_AGGREGATION.TYPE.DAILY') && $startAt->month !== $endAt->month) {
            $query->addSelect(DB::raw('month(start_at) as carry_over'));
        }

        return $query;
    }

    /**
     * いいね数を集計して取得
     *
     * @param  CarbonImmutable  $startAt
     * @param  CarbonImmutable  $endAt
     * @param  int  $type
     * @return Collection<int, LikeAggregate>
     */
    public function getForAggregation(CarbonImmutable $startAt, CarbonImmutable $endAt, int $type): Collection
    {
        return self::query()
            ->join('likes', 'likes.photo_id', '=', 'like_aggregates.photo_id')
            ->forAggregation($startAt, $endAt, $type)
            ->select([
                'like_aggregates.photo_id',
                DB::raw('CAST(sum(like_aggregates.likes) AS SIGNED) as likes'),
            ])
            ->addSelectWhenDailyAndDiffMonth($startAt, $endAt, $type)
            ->groupBy(['like_aggregates.photo_id', DB::raw('month(start_at)')])
            ->get();
    }

    /**
     * 集計したいいね数を登録
     *
     * @param  array  $likeInfo
     * @param  CarbonImmutable  $startAt
     * @param  CarbonImmutable  $endAt
     * @param  int  $type
     * @return void
     */
    public function registerForAggregation(
        array $likeInfo,
        CarbonImmutable $startAt,
        CarbonImmutable $endAt,
        int $type
    ): void {
        self::query()->create([
            'photo_id'       => $likeInfo['photo_id'],
            'aggregate_type' => $type,
            'likes'          => $likeInfo['likes'],
            'start_at'       => $startAt,
            'end_at'         => $endAt,
        ]);
    }

    /**
     * 集計したいいね数を更新
     *
     * @param  string  $photoId
     * @param  CarbonImmutable  $startAt
     * @param  CarbonImmutable  $endAt
     * @param  int  $type
     * @param  array  $value
     * @return void
     */
    public function updateForAggregation(
        string $photoId,
        CarbonImmutable $startAt,
        CarbonImmutable $endAt,
        int $type,
        array $value
    ): void {
        self::query()
            ->where('photo_id', $photoId)
            ->forAggregation($startAt, $endAt, $type)
            ->update($value);
    }
}
