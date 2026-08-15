<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReputationService
{
    public function calculateForUser(User $user): float
    {
        $friends = DB::table('relationships')->where('user_id', $user->id)->count();
        $sharedHobbies = DB::table('relationships as relationships')
            ->join('user_hobbies as own_hobbies', 'own_hobbies.user_id', '=', 'relationships.user_id')
            ->join('user_hobbies as friend_hobbies', function ($join): void {
                $join->on('friend_hobbies.user_id', '=', 'relationships.friend_id')
                    ->on('friend_hobbies.hobby_id', '=', 'own_hobbies.hobby_id');
            })
            ->where('relationships.user_id', $user->id)
            ->count();
        $blocked = DB::table('blocked_relationships')->where('user_id', $user->id)->count();
        $accountAgeDays = max(0, $user->created_at->startOfDay()->diffInDays(now()->startOfDay()));
        $accountAgeContribution = min($accountAgeDays / 30, 3);

        return round($friends + ($sharedHobbies * 0.5) + $accountAgeContribution - $blocked, 2);
    }

    public function recalculateForUser(User $user): User
    {
        $user->forceFill(['reputation_score' => $this->calculateForUser($user)])->save();

        return $user;
    }

    /** @param iterable<string> $userIds */
    public function recalculateForUsers(iterable $userIds): void
    {
        User::query()
            ->whereIn('id', collect($userIds)->unique()->values())
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->each(fn (User $user) => $this->recalculateForUser($user));
    }

    public function recalculateAll(): void
    {
        User::query()->orderBy('id')->chunkById(100, function (Collection $users): void {
            DB::transaction(fn () => $users->each(fn (User $user) => $this->recalculateForUser($user)));
        }, 'id');
    }

    public function calculateSystemMetrics(): array
    {
        $users = User::query();
        $relationshipRows = DB::table('relationships')->count();
        $sharedRows = DB::table('relationships as relationships')
            ->join('user_hobbies as own_hobbies', 'own_hobbies.user_id', '=', 'relationships.user_id')
            ->join('user_hobbies as friend_hobbies', function ($join): void {
                $join->on('friend_hobbies.user_id', '=', 'relationships.friend_id')
                    ->on('friend_hobbies.hobby_id', '=', 'own_hobbies.hobby_id');
            })
            ->count();

        return [
            'total_users' => $users->count(),
            'average_reputation' => round((float) $users->avg('reputation_score'), 2),
            'highest_reputation' => (float) ($users->max('reputation_score') ?? 0),
            'lowest_reputation' => (float) ($users->min('reputation_score') ?? 0),
            'total_relationships' => intdiv($relationshipRows, 2),
            'total_shared_hobby_pairs' => intdiv($sharedRows, 2),
            'blocked_relationships' => DB::table('blocked_relationships')->count(),
        ];
    }
}
