<?php

namespace Database\Factories;

use App\Models\Album;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'album_id' => Album::all()->random()->id,
            'author' => $this->faker->name(),
            'text' => $this->faker->text(),
        ];
    }
}
