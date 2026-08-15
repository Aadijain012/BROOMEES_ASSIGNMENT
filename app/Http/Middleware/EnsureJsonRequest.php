<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureJsonRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'DELETE'], true)) {
            return $next($request);
        }

        if (! $request->isJson()) {
            return ApiResponse::error('UNSUPPORTED_CONTENT_TYPE', 'Content-Type must be application/json.', 400);
        }

        $content = $request->getContent();

        if ($content !== '') {
            json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return ApiResponse::error('MALFORMED_JSON', 'The request body contains malformed JSON.', 400);
            }
        }

        return $next($request);
    }
}
