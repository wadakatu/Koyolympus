<?php

declare(strict_types=1);

namespace App\Http\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class Like extends Model
{
    protected $guarded = ['id'];

    public function getAllLike(string $uuid): int
    {
        return Like::query()->firstOrCreate(['photo_id' => $uuid])->all_likes ?? 0;
    }

    public function addLike(string $uuid): void
    {
        Like::query()->where('photo_id', $uuid)->first()->update([
            'likes' => DB::raw('likes + 1'),
            'all_likes' => DB::raw('all_likes + 1'),
        ]);
    }

    public function subLike(string $uuid): void
    {
        $target = Like::query()->where('photo_id', $uuid)->first();

        $target->decrement('likes');
        $target->decrement('all_likes');

        if ($target->likes < 0) {
            $target->fill(['likes' => 0])->save();
        }
    }

    public function saveByPhotoId(string $photoId, array $value): void
    {
        self::query()->where('photo_id', $photoId)->first()->fill($value)->save();
    }

    public function deleteByPhotoId(string $photoId): void
    {
        self::query()
            ->where('photo_id', $photoId)
            ->delete();
    }

    public function getForDailyAggregation(): Collection
    {
        return self::query()
            ->join('photos', 'photos.id', '=', 'likes.photo_id')
            ->where('likes', '>', 0)
            ->select(['likes.photo_id', 'likes'])
            ->groupBy('photo_id', 'likes')
            ->get();
    }
}
