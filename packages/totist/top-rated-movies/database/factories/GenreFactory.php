<?php

namespace Totist\TopRatedMovies\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Totist\TopRatedMovies\Models\Genre;

class GenreFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Genre::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => 18,
            'name' => 'Drama',
        ];
    }
}
