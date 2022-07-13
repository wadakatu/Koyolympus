<?php

declare(strict_types=1);

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class Like extends Model
{
    protected $guarded = ['id'];

    /**
     * 写真IDを基に総いいね数を取得
     *
     * @param string $uuid
     * @return int
     */
    public function getAllLike(string $uuid): int
    {
        return Like::query()->firstOrCreate(['photo_id' => $uuid])->all_likes ?? 0;
    }

    /**
     * いいね数を1増加
     *
     * @param string $uuid
     * @return void
     */
    public function addLike(string $uuid): void
    {
        Like::query()->where('photo_id', $uuid)->first()->update([
            'likes' => DB::raw('likes + 1'),
            'all_likes' => DB::raw('all_likes + 1'),
        ]);
    }

    /**
     * いいね数を1減少
     *
     * @param string $uuid
     * @return void
     */
    public function subLike(string $uuid): void
    {
        $target = Like::query()->where('photo_id', $uuid)->first();

        $target->decrement('likes');
        $target->decrement('all_likes');

        if ($target->likes < 0) {
            $target->fill(['likes' => 0])->save();
        }
    }

    /**
     * 写真IDを条件にいいね情報を更新
     *
     * @param string $photoId
     * @param array $value
     * @return void
     */
    public function saveByPhotoId(string $photoId, array $value): void
    {
        self::query()->where('photo_id', $photoId)->first()->fill($value)->save();
    }

    /**
     * 写真IDを条件にいいね情報を削除
     *
     * @param string $photoId
     * @return void
     * @throws \Exception
     */
    public function deleteByPhotoId(string $photoId): void
    {
        self::query()
            ->where('photo_id', $photoId)
            ->delete();
    }

    /**
     * 日毎のいいね数をまとめて返却
     *
     * @return Collection
     */
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
