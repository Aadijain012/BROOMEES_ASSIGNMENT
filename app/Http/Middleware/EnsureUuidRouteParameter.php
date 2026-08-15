<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureUuidRouteParameter
{
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->route('id');

        if ($id !== null && ! Str::isUuid($id)) {
            return ApiResponse::error('INVALID_UUID', 'The route identifier must be a UUID.', 400);
        }

        return $next($request);
    }
}
