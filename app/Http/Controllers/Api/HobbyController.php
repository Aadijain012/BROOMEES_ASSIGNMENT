<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\HobbyRequest;
use App\Repositories\UserRepository;
use App\Services\HobbyService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class HobbyController extends Controller
{
    public function store(HobbyRequest $request, string $id, UserRepository $users, HobbyService $hobbies): JsonResponse
    {
        $hobbies->assign($users->findWithDetails($id), $request->string('hobby_id')->toString());

        return ApiResponse::success(['message' => 'Hobby assigned.'], 201);
    }

    public function destroy(HobbyRequest $request, string $id, UserRepository $users, HobbyService $hobbies): JsonResponse
    {
        $hobbies->remove($users->findWithDetails($id), $request->string('hobby_id')->toString());

        return ApiResponse::success(['message' => 'Hobby removed.']);
    }
}
