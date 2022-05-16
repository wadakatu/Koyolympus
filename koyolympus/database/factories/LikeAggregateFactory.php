<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\Models\LikeAggregate;

$factory->define(LikeAggregate::class, function (Faker $faker) {
    return [
        'photo_id' => Str::random(12),
        'aggregate_type' => $faker->numberBetween(1, 3),
        'likes' => $faker->numberBetween(0, 500),
        'status' => $faker->numberBetween(0, 1),
        'start_at' => $faker->date(),
        'end_at' => $faker->date()
    ];
});
