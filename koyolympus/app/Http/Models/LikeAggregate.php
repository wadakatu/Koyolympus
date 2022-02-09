<?php
declare(strict_types=1);

namespace App\Http\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class LikeAggregate extends Model
{
    protected $guarded = ['id'];

    public function registerAggregatedLike(Like $like, Carbon $startAt, Carbon $endAt, int $type)
    {
        self::query()->create([
            'photo_id' => $like->photo_id,
            'aggregate_type' => $type,
            'likes' => $like->likes,
            'start_at' => $startAt,
            'end_at' => $endAt
        ]);
    }
}
