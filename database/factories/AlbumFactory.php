<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AlbumFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'text' => $this->faker->text(),
            'km' => $this->faker->numberBetween(0, 1600),
            'departure_place' => $this->faker->city(),
            'arrival_place' => $this->faker->city(),
        ];
    }
}
