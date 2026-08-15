<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReputationService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ReputationMetricsController extends Controller
{
    public function __invoke(ReputationService $reputation): JsonResponse
    {
        return ApiResponse::success($reputation->calculateSystemMetrics());
    }
}
