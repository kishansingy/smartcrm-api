<?php

namespace App\Domain\Dashboard\Contracts;

interface DashboardRepositoryInterface
{
    public function getSummary(int $tenantId, int $userId): array;
    public function getLeadStats(int $tenantId): array;
    public function getDealStats(int $tenantId): array;
    public function getTaskStats(int $tenantId, int $userId): array;
    public function getRevenueChart(int $tenantId, string $period): array;
    public function getLeadsBySource(int $tenantId): array;
    public function getLeadsByStatus(int $tenantId): array;
    public function getDealsByStage(int $tenantId): array;
    public function getTopPerformers(int $tenantId, int $limit = 5): array;
    public function getRecentActivities(int $tenantId, int $limit = 10): array;
    public function getUpcomingTasks(int $userId, int $limit = 5): array;
    public function getConversionFunnel(int $tenantId): array;
}
