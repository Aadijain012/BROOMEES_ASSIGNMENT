<?php

namespace Database\Factories;

use App\Models\BlockedRelationship;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BlockedRelationship> */
class BlockedRelationshipFactory extends Factory
{
    protected $model = BlockedRelationship::class;

    public function definition(): array
    {
        return ['user_id' => User::factory(), 'blocked_user_id' => User::factory(), 'created_at' => now()];
    }
}
