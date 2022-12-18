<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class LikeAggregateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'photo_id'       => Str::random(12),
            'aggregate_type' => $this->faker->numberBetween(1, 3),
            'likes'          => $this->faker->numberBetween(0, 500),
            'status'         => $this->faker->numberBetween(0, 1),
            'start_at'       => $this->faker->date(),
            'end_at'         => $this->faker->date(),
        ];
    }
}
