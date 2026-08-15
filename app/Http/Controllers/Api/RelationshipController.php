<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RelationshipRequest;
use App\Repositories\UserRepository;
use App\Services\RelationshipService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class RelationshipController extends Controller
{
    public function store(RelationshipRequest $request, string $id, UserRepository $users, RelationshipService $relationships): JsonResponse
    {
        $relationships->create($users->findWithDetails($id), $request->string('friend_id')->toString());

        return ApiResponse::success(['message' => 'Relationship created.'], 201);
    }

    public function destroy(RelationshipRequest $request, string $id, UserRepository $users, RelationshipService $relationships): JsonResponse
    {
        $relationships->delete($users->findWithDetails($id), $request->string('friend_id')->toString());

        return ApiResponse::success(['message' => 'Relationship removed.']);
    }
}
