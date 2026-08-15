<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IssueTokenRequest;
use App\Services\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TokenController extends Controller
{
    public function store(IssueTokenRequest $request, AuthService $authService): JsonResponse
    {
        $issuedToken = $authService->issueToken($request->string('username')->toString(), $request->string('password')->toString());

        if ($issuedToken === null) {
            Log::warning('Authentication failed', ['username' => $request->string('username')->toString()]);

            return ApiResponse::error('INVALID_CREDENTIALS', 'The provided credentials are invalid.', 401);
        }

        return ApiResponse::success($issuedToken, 201);
    }
}
