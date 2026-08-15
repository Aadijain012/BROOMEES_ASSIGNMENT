<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $presentedToken = $request->bearerToken();

        if (! is_string($presentedToken) || $presentedToken === '') {
            return ApiResponse::error('INVALID_TOKEN', 'A valid bearer token is required.', 401);
        }

        $tokenHash = hash('sha256', $presentedToken);
        $token = ApiToken::query()->with('user')->where('token_hash', $tokenHash)->first();

        if ($token === null || ! hash_equals($token->token_hash, $tokenHash) || ! $token->isActive()) {
            return ApiResponse::error('INVALID_TOKEN', 'A valid bearer token is required.', 401);
        }

        $token->forceFill(['last_used_at' => now()])->save();
        $request->setUserResolver(fn () => $token->user);
        $request->attributes->set('apiToken', $token);

        return $next($request);
    }
}
