<?php

namespace App\Application\Dashboard\Services;

use App\Domain\Dashboard\Contracts\DashboardRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function __construct(
        private readonly DashboardRepositoryInterface $dashboardRepository,
    ) {}

    public function overview(): array
    {
        $user     = Auth::user();
        $tenantId = $user->tenant_id;
        $userId   = $user->id;

        // Cache for 5 minutes per tenant
        return Cache::remember("dashboard.overview.{$tenantId}.{$userId}", 300, function () use ($tenantId, $userId) {
            return [
                'summary'           => $this->dashboardRepository->getSummary($tenantId, $userId),
                'upcoming_tasks'    => $this->dashboardRepository->getUpcomingTasks($userId, 5),
                'recent_activities' => $this->dashboardRepository->getRecentActivities($tenantId, 10),
                'top_performers'    => $this->dashboardRepository->getTopPerformers($tenantId, 5),
            ];
        });
    }

    public function revenueChart(string $period = 'monthly'): array
    {
        $tenantId = Auth::user()->tenant_id;

        return Cache::remember("dashboard.revenue.{$tenantId}.{$period}", 600, function () use ($tenantId, $period) {
            return $this->dashboardRepository->getRevenueChart($tenantId, $period);
        });
    }

    public function leadCharts(): array
    {
        $tenantId = Auth::user()->tenant_id;

        return Cache::remember("dashboard.leads.charts.{$tenantId}", 300, function () use ($tenantId) {
            return [
                'by_source' => $this->dashboardRepository->getLeadsBySource($tenantId),
                'by_status' => $this->dashboardRepository->getLeadsByStatus($tenantId),
                'funnel'    => $this->dashboardRepository->getConversionFunnel($tenantId),
            ];
        });
    }

    public function dealCharts(): array
    {
        $tenantId = Auth::user()->tenant_id;

        return Cache::remember("dashboard.deals.charts.{$tenantId}", 300, function () use ($tenantId) {
            return [
                'by_stage' => $this->dashboardRepository->getDealsByStage($tenantId),
            ];
        });
    }

    public function flush(): void
    {
        $tenantId = Auth::user()->tenant_id;
        $userId   = Auth::id();

        Cache::forget("dashboard.overview.{$tenantId}.{$userId}");
        Cache::forget("dashboard.revenue.{$tenantId}.monthly");
        Cache::forget("dashboard.leads.charts.{$tenantId}");
        Cache::forget("dashboard.deals.charts.{$tenantId}");
    }
}
