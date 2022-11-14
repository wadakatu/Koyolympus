<?php

declare(strict_types=1);

namespace Database\Factories;

use Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $uuid = Str::uuid()->toString();
        return [
            'id' => $uuid,
            'file_name' => "$uuid-factory.jpeg",
            'file_path' => '/photo/factory',
            'genre' => 1,
            'created_at' => $this->faker->dateTime
        ];
    }
}
