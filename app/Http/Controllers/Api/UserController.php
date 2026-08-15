<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\UserService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function index(Request $request, UserRepository $users): JsonResponse
    {
        $perPage = min(
            max((int) $request->query('per_page', config('business.pagination.default_per_page')), 1),
            config('business.pagination.max_per_page'),
        );
        $paginator = $users->paginate($request->query('search'), $perPage);

        return ApiResponse::success(
            $paginator->getCollection()->map(fn (User $user) => (new UserResource($user))->resolve())->values(),
            200,
            [
                'pagination' => [
                    'page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                ],
            ],
        );
    }

    public function show(string $id, UserRepository $users): JsonResponse
    {
        return ApiResponse::success((new UserResource($users->findWithDetails($id)))->resolve());
    }

    public function store(StoreUserRequest $request, UserService $users): JsonResponse
    {
        $user = $users->create($request->validated());

        return ApiResponse::success((new UserResource($user))->resolve(), 201);
    }

    public function update(UpdateUserRequest $request, string $id, UserRepository $repository, UserService $users): JsonResponse
    {
        $user = $users->update($repository->findWithDetails($id), $request->validated());

        return ApiResponse::success((new UserResource($user))->resolve());
    }

    public function destroy(string $id, UserRepository $repository, UserService $users): Response
    {
        $users->delete($repository->findWithDetails($id));

        return response()->noContent();
    }
}
