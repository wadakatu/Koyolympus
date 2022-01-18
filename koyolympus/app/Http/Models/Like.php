<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $guarded = ['id'];

    public function getAllLike(string $uuid): int
    {
        return Like::query()->firstOrCreate(['photo_id' => $uuid])->all_likes ?? 0;
    }

    public function addLike(string $uuid): void
    {
        Like::query()->where('photo_id', $uuid)->first()->increment('all_likes');
    }

    public function subLike(string $uuid): void
    {
        $target = Like::query()->where('photo_id', $uuid)->first();
        $likes = $target->decrement('all_likes');

        if ($likes < 0) {
            $target->save(['all_likes' => 0]);
        }
    }
}
