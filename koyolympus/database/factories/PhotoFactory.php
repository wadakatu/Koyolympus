<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Photo;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Photo::class, function (Faker $faker) {
    $uuid = Str::uuid()->toString();
    return [
        'id' => $uuid,
        'file_name' => "$uuid-factory.jpeg",
        'file_path' => '/photo/factory',
        'genre' => 1,
        'created_at' => $faker->dateTime
    ];
});
