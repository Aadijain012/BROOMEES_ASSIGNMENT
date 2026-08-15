<?php

namespace App\Services;

use App\Exceptions\OptimisticLockException;
use App\Exceptions\UserDeletionConflictException;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function create(array $attributes): User
    {
        return User::query()->create($attributes);
    }

    public function update(User $user, array $attributes): User
    {
        $version = (int) $attributes['version'];
        $updates = Arr::except($attributes, ['version']);
        $updates['version'] = DB::raw('version + 1');
        $updates['updated_at'] = now();

        $updated = User::query()
            ->whereKey($user->id)
            ->where('version', $version)
            ->update($updates);

        if ($updated !== 1) {
            Log::warning('Optimistic lock conflict', ['user_id' => $user->id, 'supplied_version' => $version]);
            throw new OptimisticLockException;
        }

        return $user->fresh(['hobbies', 'friends']);
    }

    public function delete(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            $hasRelationships = DB::table('relationships')
                ->where('user_id', $lockedUser->id)
                ->orWhere('friend_id', $lockedUser->id)
                ->exists();

            if ($hasRelationships) {
                Log::warning('User deletion conflict', ['user_id' => $lockedUser->id, 'reason' => 'active_relationships']);
                throw new UserDeletionConflictException('A user with active relationships cannot be deleted.');
            }

            if ($lockedUser->reputation_score > config('business.deletion.max_reputation')) {
                Log::warning('User deletion conflict', ['user_id' => $lockedUser->id, 'reason' => 'reputation_threshold']);
                throw new UserDeletionConflictException('A user above the configured reputation threshold cannot be deleted.');
            }

            $lockedUser->delete();
        });
    }
}
