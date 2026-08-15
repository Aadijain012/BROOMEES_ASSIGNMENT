<?php

namespace Database\Factories;

use App\Models\Hobby;
use App\Models\User;
use App\Models\UserHobby;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<UserHobby> */
class UserHobbyFactory extends Factory
{
    protected $model = UserHobby::class;

    public function definition(): array
    {
        return ['user_id' => User::factory(), 'hobby_id' => Hobby::factory(), 'created_at' => now()];
    }
}
