<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Application\Dashboard\Services\DashboardService;
use App\Http\Controllers\Controller;
use App\Support\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService) {}

    /**
     * Full dashboard overview — summary cards + upcoming tasks + recent activities + top performers.
     */
    public function overview(): JsonResponse
    {
        return ApiResponse::success($this->dashboardService->overview());
    }

    /**
     * Revenue chart data for the last 6 months.
     */
    public function revenueChart(Request $request): JsonResponse
    {
        $period = $request->query('period', 'monthly');

        return ApiResponse::success($this->dashboardService->revenueChart($period));
    }

    /**
     * Lead charts — by source, by status, conversion funnel.
     */
    public function leadCharts(): JsonResponse
    {
        return ApiResponse::success($this->dashboardService->leadCharts());
    }

    /**
     * Deal charts — pipeline stage distribution.
     */
    public function dealCharts(): JsonResponse
    {
        return ApiResponse::success($this->dashboardService->dealCharts());
    }

    /**
     * Flush dashboard cache — call after bulk imports or major updates.
     */
    public function refresh(): JsonResponse
    {
        $this->dashboardService->flush();

        return ApiResponse::success(null, 'Dashboard cache refreshed.');
    }
}
