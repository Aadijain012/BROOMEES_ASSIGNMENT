<?php

namespace Database\Factories;

use App\Models\Relationship;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Relationship> */
class RelationshipFactory extends Factory
{
    protected $model = Relationship::class;

    public function definition(): array
    {
        return ['user_id' => User::factory(), 'friend_id' => User::factory()];
    }
}
