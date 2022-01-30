<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Http\Models\Like;
use Faker\Generator as Faker;

$factory->define(Like::class, function (Faker $faker) {
    return [
        'photo_id' => Str::random(12),
        'likes' => $faker->numberBetween(1, 100),
        'week_likes' => $faker->numberBetween(1, 100),
        'month_likes' => $faker->numberBetween(1, 100),
        'all_likes' => $faker->numberBetween(1, 100)
    ];
});
