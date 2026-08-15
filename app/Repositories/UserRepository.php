<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository
{
    public function paginate(?string $search, int $perPage): LengthAwarePaginator
    {
        return User::query()
            ->with(['hobbies', 'friends'])
            ->when($search, fn ($query) => $query->where('username', 'like', '%'.$search.'%'))
            ->orderBy('username')
            ->paginate($perPage);
    }

    public function findWithDetails(string $id): User
    {
        return User::query()->with(['hobbies', 'friends'])->findOrFail($id);
    }
}
