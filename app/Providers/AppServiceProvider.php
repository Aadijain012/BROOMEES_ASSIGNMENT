<?php

namespace App\Providers;

use App\Support\ApiResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach (['api-read', 'api-write'] as $limiter) {
            RateLimiter::for($limiter, function (Request $request) use ($limiter): Limit {
                $tokenId = (string) optional($request->attributes->get('apiToken'))->id;
                $bearerTokenHash = $request->bearerToken() !== null ? hash('sha256', $request->bearerToken()) : null;
                $limit = $limiter === 'api-read'
                    ? config('business.rate_limits.read_per_minute')
                    : config('business.rate_limits.write_per_minute');

                return Limit::perMinute($limit)
                    ->by($tokenId !== '' ? "token:{$tokenId}" : ($bearerTokenHash !== null ? "token-hash:{$bearerTokenHash}" : "anonymous:{$request->ip()}"))
                    ->response(function (Request $request, array $headers) use ($limiter) {
                        Log::warning('API rate limit exceeded', [
                            'limiter' => $limiter,
                            'token_id' => optional($request->attributes->get('apiToken'))->id,
                        ]);

                        return ApiResponse::error('RATE_LIMIT_EXCEEDED', 'Too many requests. Please retry later.', 429)
                            ->withHeaders($headers);
                    });
            });
        }
    }
}
