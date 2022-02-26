<?php
declare(strict_types=1);

namespace App\Http\Models;

use DB;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

class LikeAggregate extends Model
{
    protected $guarded = ['id'];

    public function getForAggregation(CarbonImmutable $startAt, CarbonImmutable $endAt, int $type)
    {
        return self::query()
            ->join('likes', 'likes.photo_id', '=', 'like_aggregates.photo_id')
            ->where('aggregate_type', $type)
            ->whereBetween('start_at', [$startAt, $endAt])
            ->whereBetween('end_at', [$startAt, $endAt])
            ->select([
                'likes.id',
                'like_aggregates.id as like_aggregate_id',
                'like_aggregates.photo_id',
                DB::raw('CAST(sum(like_aggregates.likes) AS SIGNED) as likes')
            ])
            ->groupBy('likes.id', 'like_aggregates.id', 'like_aggregates.photo_id')
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

    public function saveById(int $id, array $value)
    {
        self::query()->find($id)->fill($value)->save();
    }
}
