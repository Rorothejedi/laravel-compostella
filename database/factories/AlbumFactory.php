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
            'date' => $this->faker->date(),
            'place_departure' => $this->faker->city(),
            'place_arrival' => $this->faker->city(),
            'km_step' => $this->faker->randomFloat(1, 10, 40),
            'km_total' => $this->faker->randomFloat(1, 0, 1500),
        ];
    }
}
