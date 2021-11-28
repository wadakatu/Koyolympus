<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $guarded = ['id'];

    public function getAllLike(string $uuid): int
    {
        $query = Like::query();
        return $query->firstOrCreate(['photo_id' => $uuid])->all_likes ?? 0;
    }
}
