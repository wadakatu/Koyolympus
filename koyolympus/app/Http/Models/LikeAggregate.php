<?php

declare(strict_types=1);

namespace App\Http\Models;

use DB;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class LikeAggregate extends Model
{
    protected $guarded = ['id'];

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


    public function scopeAddSelectWhenDailyAndDiffMonth(
        Builder $query,
        CarbonImmutable $startAt,
        CarbonImmutable $endAt,
        int $type
    ): Builder {
        return $query->when(
            $type === config('const.PHOTO_AGGREGATION.TYPE.DAILY')
            && $startAt->month !== $endAt->month,
            function (Builder $query): Builder {
                return $query->addSelect(DB::raw('month(start_at) as carry_over'));
            }
        );
    }

    /**
     * @param CarbonImmutable $startAt
     * @param CarbonImmutable $endAt
     * @param int $type
     * @return Collection
     */
    public function getForAggregation(CarbonImmutable $startAt, CarbonImmutable $endAt, int $type): Collection
    {
        return self::query()
            ->join('likes', 'likes.photo_id', '=', 'like_aggregates.photo_id')
            ->forAggregation($startAt, $endAt, $type)
            ->select([
                'like_aggregates.photo_id',
                DB::raw('CAST(sum(like_aggregates.likes) AS SIGNED) as likes')
            ])
            ->addSelectWhenDailyAndDiffMonth($startAt, $endAt, $type)
            ->groupBy(['like_aggregates.photo_id', DB::raw("month(start_at)")])
            ->get();
    }

    public function registerForAggregation(
        array $likeInfo,
        CarbonImmutable $startAt,
        CarbonImmutable $endAt,
        int $type
    ): void {
        self::query()->create([
            'photo_id' => $likeInfo['photo_id'],
            'aggregate_type' => $type,
            'likes' => $likeInfo['likes'],
            'start_at' => $startAt,
            'end_at' => $endAt
        ]);
    }

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
