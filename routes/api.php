<?php

use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\HobbyController;
use App\Http\Controllers\Api\OpenApiController;
use App\Http\Controllers\Api\RelationshipController;
use App\Http\Controllers\Api\ReputationMetricsController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.json')->group(function (): void {
    Route::get('/health', HealthController::class);
    Route::get('/documentation', [OpenApiController::class, 'documentation']);
    Route::get('/openapi.yaml', [OpenApiController::class, 'specification']);
    Route::post('/auth/token', [TokenController::class, 'store']);
    Route::post('/users', [UserController::class, 'store']);

    Route::middleware(['api.token', 'uuid.route'])->group(function (): void {
        Route::middleware('throttle:api-read')->group(function (): void {
            Route::get('/users', [UserController::class, 'index']);
            Route::get('/users/{id}', [UserController::class, 'show']);
            Route::get('/metrics/reputation', ReputationMetricsController::class);
        });

        Route::middleware(['throttle:api-write', 'api.owner'])->group(function (): void {
            Route::put('/users/{id}', [UserController::class, 'update']);
            Route::delete('/users/{id}', [UserController::class, 'destroy']);
            Route::post('/users/{id}/relationships', [RelationshipController::class, 'store']);
            Route::delete('/users/{id}/relationships', [RelationshipController::class, 'destroy']);
            Route::post('/users/{id}/hobbies', [HobbyController::class, 'store']);
            Route::delete('/users/{id}/hobbies', [HobbyController::class, 'destroy']);
        });
    });
});
