<?php

use App\Exceptions\ApiDomainException;
use App\Http\Middleware\AuthenticateApiToken;
use App\Http\Middleware\EnsureJsonRequest;
use App\Http\Middleware\EnsureOwnUserResource;
use App\Http\Middleware\EnsureUuidRouteParameter;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'api.json' => EnsureJsonRequest::class,
            'api.token' => AuthenticateApiToken::class,
            'api.owner' => EnsureOwnUserResource::class,
            'uuid.route' => EnsureUuidRouteParameter::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*'));

        $exceptions->render(function (ApiDomainException $exception, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error($exception->errorCode, $exception->getMessage(), $exception->status);
            }
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('RESOURCE_NOT_FOUND', 'The requested resource was not found.', 404);
            }
        });
    })->create();
