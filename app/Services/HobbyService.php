<?php

namespace App\Services;

use App\Exceptions\ApiDomainException;
use App\Models\Hobby;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class HobbyService
{
    public function __construct(private readonly ReputationService $reputation) {}

    public function assign(User $user, string $hobbyId): void
    {
        DB::transaction(function () use ($user, $hobbyId): void {
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            Hobby::query()->findOrFail($hobbyId);

            try {
                DB::table('user_hobbies')->insert([
                    'user_id' => $lockedUser->id,
                    'hobby_id' => $hobbyId,
                    'created_at' => now(),
                ]);
            } catch (QueryException $exception) {
                throw new ApiDomainException('HOBBY_ALREADY_ASSIGNED', 'This hobby is already assigned to the user.', 409);
            }

            $this->recalculateAffectedUsers($lockedUser);
        });
    }

    public function remove(User $user, string $hobbyId): void
    {
        DB::transaction(function () use ($user, $hobbyId): void {
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $deleted = DB::table('user_hobbies')
                ->where('user_id', $lockedUser->id)
                ->where('hobby_id', $hobbyId)
                ->delete();

            if ($deleted !== 1) {
                throw new ApiDomainException('HOBBY_ASSIGNMENT_NOT_FOUND', 'The hobby is not assigned to the user.', 404);
            }

            $this->recalculateAffectedUsers($lockedUser);
        });
    }

    private function recalculateAffectedUsers(User $user): void
    {
        $friendIds = DB::table('relationships')->where('user_id', $user->id)->pluck('friend_id');
        $this->reputation->recalculateForUsers($friendIds->push($user->id));
    }
}
