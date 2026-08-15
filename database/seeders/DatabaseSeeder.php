<?php

namespace Database\Seeders;

use App\Models\Hobby;
use App\Models\User;
use App\Services\RelationshipService;
use App\Services\ReputationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $hobbies = collect(['football', 'music', 'coding', 'gaming', 'reading', 'cooking', 'hiking', 'photography'])
            ->mapWithKeys(fn (string $name) => [$name => Hobby::query()->firstOrCreate(['name' => $name])]);

        $usernames = ['alice', 'bob', 'carol', 'david', 'elena', 'farhan', 'grace', 'harry', 'isha', 'james'];
        $users = collect($usernames)->mapWithKeys(fn (string $username, int $index) => [
            $username => User::query()->firstOrCreate(
                ['username' => $username],
                ['age' => 22 + $index, 'password' => Hash::make('DemoPassword123!')],
            ),
        ]);

        $assignments = [
            'alice' => ['football', 'music', 'coding'], 'bob' => ['music', 'coding', 'gaming'],
            'carol' => ['reading', 'cooking'], 'david' => ['football', 'hiking'],
            'elena' => ['coding', 'photography'], 'farhan' => ['gaming', 'music'],
            'grace' => ['reading', 'hiking'], 'harry' => ['cooking', 'photography'],
            'isha' => ['music', 'reading'], 'james' => ['football', 'gaming'],
        ];

        foreach ($assignments as $username => $names) {
            $users[$username]->hobbies()->syncWithoutDetaching(collect($names)->map(fn (string $name) => $hobbies[$name]->id));
        }

        $relationships = app(RelationshipService::class);
        foreach ([['alice', 'bob'], ['alice', 'carol'], ['bob', 'david'], ['elena', 'farhan'], ['grace', 'isha']] as [$left, $right]) {
            $relationships->create($users[$left], $users[$right]->id);
        }

        DB::table('blocked_relationships')->insert([
            ['user_id' => $users['alice']->id, 'blocked_user_id' => $users['james']->id, 'created_at' => now()],
            ['user_id' => $users['farhan']->id, 'blocked_user_id' => $users['harry']->id, 'created_at' => now()],
        ]);

        app(ReputationService::class)->recalculateAll();
    }
}
