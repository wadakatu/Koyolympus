<?php

declare(strict_types=1);

namespace Database\Factories;

use Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class LikeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'photo_id' => Str::random(12),
            'likes' => $this->faker->numberBetween(1, 100),
            'week_likes' => $this->faker->numberBetween(1, 100),
            'month_likes' => $this->faker->numberBetween(1, 100),
            'all_likes' => $this->faker->numberBetween(1, 100)
        ];
    }
}
