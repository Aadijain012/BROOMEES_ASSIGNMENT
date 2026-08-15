<?php

namespace Database\Factories;

use App\Models\Hobby;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Hobby> */
class HobbyFactory extends Factory
{
    protected $model = Hobby::class;

    public function definition(): array
    {
        return ['name' => fake()->unique()->word()];
    }
}
