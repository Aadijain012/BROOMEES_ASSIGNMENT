<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnUserResource
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->id !== $request->route('id')) {
            return ApiResponse::error('FORBIDDEN', 'You are not allowed to modify this user resource.', 403);
        }

        return $next($request);
    }
}
