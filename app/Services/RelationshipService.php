<?php

namespace App\Services;

use App\Exceptions\ApiDomainException;
use App\Exceptions\RelationshipAlreadyExistsException;
use App\Exceptions\RelationshipNotFoundException;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RelationshipService
{
    public function __construct(private readonly ReputationService $reputation) {}

    public function create(User $user, string $friendId): void
    {
        if ($user->id === $friendId) {
            throw new ApiDomainException('SELF_RELATIONSHIP', 'A user cannot create a relationship with themselves.', 400);
        }

        DB::transaction(function () use ($user, $friendId): void {
            $ids = collect([$user->id, $friendId])->sort()->values();
            $lockedUsers = User::query()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();

            if ($lockedUsers->count() !== 2) {
                throw new ApiDomainException('RESOURCE_NOT_FOUND', 'The requested friend was not found.', 404);
            }

            try {
                DB::table('relationships')->insert([
                    ['user_id' => $user->id, 'friend_id' => $friendId, 'created_at' => now(), 'updated_at' => now()],
                    ['user_id' => $friendId, 'friend_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
                ]);
            } catch (QueryException $exception) {
                Log::warning('Relationship conflict', ['user_id' => $user->id, 'friend_id' => $friendId]);
                throw new RelationshipAlreadyExistsException;
            }

            $this->reputation->recalculateForUsers($ids);
        });
    }

    public function delete(User $user, string $friendId): void
    {
        DB::transaction(function () use ($user, $friendId): void {
            $ids = collect([$user->id, $friendId])->sort()->values();
            $lockedUsers = User::query()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();

            if ($lockedUsers->count() !== 2) {
                throw new ApiDomainException('RESOURCE_NOT_FOUND', 'The requested friend was not found.', 404);
            }

            $deleted = DB::table('relationships')
                ->where('user_id', $user->id)
                ->where('friend_id', $friendId)
                ->delete();

            if ($deleted !== 1) {
                throw new RelationshipNotFoundException;
            }

            DB::table('relationships')
                ->where('user_id', $friendId)
                ->where('friend_id', $user->id)
                ->delete();

            $this->reputation->recalculateForUsers($ids);
        });
    }
}
