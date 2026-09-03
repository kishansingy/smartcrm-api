<?php

namespace App\Infrastructure\Dashboard\Repositories;

use App\Domain\Dashboard\Contracts\DashboardRepositoryInterface;
use App\Domain\Lead\Models\Lead;
use App\Domain\Lead\Models\LeadActivity;
use App\Domain\Pipeline\Models\Deal;
use App\Domain\Pipeline\Models\PipelineStage;
use App\Domain\Task\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getSummary(int $tenantId, int $userId): array
    {
        return [
            'leads'    => $this->getLeadStats($tenantId),
            'deals'    => $this->getDealStats($tenantId),
            'tasks'    => $this->getTaskStats($tenantId, $userId),
            'contacts' => $this->getContactStats($tenantId),
        ];
    }

    public function getLeadStats(int $tenantId): array
    {
        $q = Lead::where('tenant_id', $tenantId);

        $thisMonth = (clone $q)->whereMonth('created_at', now()->month)
                               ->whereYear('created_at', now()->year);
        $lastMonth = (clone $q)->whereMonth('created_at', now()->subMonth()->month)
                               ->whereYear('created_at', now()->subMonth()->year);

        $thisMonthCount = $thisMonth->count();
        $lastMonthCount = $lastMonth->count();
        $growth = $lastMonthCount > 0
            ? round((($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100, 1)
            : 0;

        return [
            'total'       => (clone $q)->count(),
            'new'         => (clone $q)->where('status', 'new')->count(),
            'qualified'   => (clone $q)->where('status', 'qualified')->count(),
            'won'         => (clone $q)->where('status', 'won')->count(),
            'lost'        => (clone $q)->where('status', 'lost')->count(),
            'this_month'  => $thisMonthCount,
            'last_month'  => $lastMonthCount,
            'growth'      => $growth,
        ];
    }

    public function getDealStats(int $tenantId): array
    {
        $q = Deal::where('tenant_id', $tenantId);

        $openValue = (clone $q)->where('status', 'open')->sum('value');
        $wonValue  = (clone $q)->where('status', 'won')->sum('value');
        $wonMonth  = (clone $q)->where('status', 'won')
                               ->whereMonth('closed_at', now()->month)
                               ->whereYear('closed_at', now()->year)
                               ->sum('value');

        return [
            'total'          => (clone $q)->count(),
            'open'           => (clone $q)->where('status', 'open')->count(),
            'won'            => (clone $q)->where('status', 'won')->count(),
            'lost'           => (clone $q)->where('status', 'lost')->count(),
            'open_value'     => round((float) $openValue, 2),
            'won_value'      => round((float) $wonValue, 2),
            'won_this_month' => round((float) $wonMonth, 2),
            'avg_deal_value' => (clone $q)->where('status', 'won')->avg('value') ?? 0,
        ];
    }

    public function getTaskStats(int $tenantId, int $userId): array
    {
        $tenant = Task::where('tenant_id', $tenantId);
        $mine   = Task::where('assigned_to', $userId);

        return [
            'total'       => (clone $tenant)->count(),
            'my_pending'  => (clone $mine)->where('status', 'pending')->count(),
            'my_overdue'  => (clone $mine)->where('status', '!=', 'completed')
                                           ->whereDate('due_date', '<', now())->count(),
            'due_today'   => (clone $mine)->where('status', '!=', 'completed')
                                           ->whereDate('due_date', today())->count(),
            'completed_this_week' => (clone $mine)->where('status', 'completed')
                                                   ->whereBetween('completed_at', [
                                                       now()->startOfWeek(), now()->endOfWeek(),
                                                   ])->count(),
        ];
    }

    private function getContactStats(int $tenantId): array
    {
        $result = DB::table('contacts')
            ->where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN MONTH(created_at) = ? AND YEAR(created_at) = ? THEN 1 ELSE 0 END) as this_month
            ', [now()->month, now()->year])
            ->first();

        return $result ? (array) $result : ['total' => 0, 'active' => 0, 'this_month' => 0];
    }

    public function getRevenueChart(int $tenantId, string $period = 'monthly'): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i));

        return $months->map(function ($month) use ($tenantId) {
            $revenue = Deal::where('tenant_id', $tenantId)
                ->where('status', 'won')
                ->whereMonth('closed_at', $month->month)
                ->whereYear('closed_at', $month->year)
                ->sum('value');

            return [
                'label'   => $month->format('M Y'),
                'revenue' => round((float) $revenue, 2),
                'month'   => $month->month,
                'year'    => $month->year,
            ];
        })->values()->toArray();
    }

    public function getLeadsBySource(int $tenantId): array
    {
        return Lead::where('tenant_id', $tenantId)
            ->select('source', DB::raw('COUNT(*) as count'))
            ->groupBy('source')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => ['source' => $row->source, 'count' => $row->count])
            ->toArray();
    }

    public function getLeadsByStatus(int $tenantId): array
    {
        return Lead::where('tenant_id', $tenantId)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(fn ($row) => ['status' => $row->status, 'count' => $row->count])
            ->toArray();
    }

    public function getDealsByStage(int $tenantId): array
    {
        return PipelineStage::whereHas('pipeline', fn ($q) => $q->where('tenant_id', $tenantId))
            ->withCount(['deals' => fn ($q) => $q->where('status', 'open')])
            ->withSum(['deals' => fn ($q) => $q->where('status', 'open')], 'value')
            ->orderBy('position')
            ->get()
            ->map(fn ($stage) => [
                'stage'       => $stage->name,
                'color'       => $stage->color,
                'deals_count' => $stage->deals_count,
                'deals_value' => round((float) ($stage->deals_sum_value ?? 0), 2),
            ])
            ->toArray();
    }

    public function getTopPerformers(int $tenantId, int $limit = 5): array
    {
        return User::where('tenant_id', $tenantId)
            ->withCount(['leads as leads_count' => fn ($q) => $q->whereMonth('created_at', now()->month)])
            ->withSum(['deals as deals_won_value' => fn ($q) => $q
                ->where('status', 'won')
                ->whereMonth('closed_at', now()->month)], 'value')
            ->orderByDesc('deals_won_value')
            ->limit($limit)
            ->get()
            ->map(fn ($user) => [
                'id'              => $user->id,
                'name'            => $user->name,
                'avatar'          => $user->avatar,
                'leads_count'     => $user->leads_count,
                'deals_won_value' => round((float) ($user->deals_won_value ?? 0), 2),
            ])
            ->toArray();
    }

    public function getRecentActivities(int $tenantId, int $limit = 10): array
    {
        return LeadActivity::whereHas('lead', fn ($q) => $q->where('tenant_id', $tenantId))
            ->with(['lead:id,first_name,last_name', 'user:id,name,avatar'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($a) => [
                'id'          => $a->id,
                'type'        => $a->type,
                'description' => $a->description,
                'lead'        => $a->lead ? [
                    'id'   => $a->lead->id,
                    'name' => $a->lead->full_name,
                ] : null,
                'user'       => $a->user ? [
                    'id'     => $a->user->id,
                    'name'   => $a->user->name,
                    'avatar' => $a->user->avatar,
                ] : null,
                'created_at' => $a->created_at->toISOString(),
            ])
            ->toArray();
    }

    public function getUpcomingTasks(int $userId, int $limit = 5): array
    {
        return Task::with(['lead:id,first_name,last_name', 'contact:id,first_name,last_name'])
            ->where('assigned_to', $userId)
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '>=', now())
            ->orderBy('due_date')
            ->limit($limit)
            ->get()
            ->map(fn ($task) => [
                'id'       => $task->id,
                'title'    => $task->title,
                'type'     => $task->type,
                'priority' => $task->priority,
                'due_date' => $task->due_date->toDateString(),
                'due_time' => $task->due_time,
                'related'  => $task->lead
                    ? ['type' => 'lead', 'id' => $task->lead->id, 'name' => $task->lead->full_name]
                    : ($task->contact ? ['type' => 'contact', 'id' => $task->contact->id, 'name' => $task->contact->full_name] : null),
            ])
            ->toArray();
    }

    public function getConversionFunnel(int $tenantId): array
    {
        $leads    = Lead::where('tenant_id', $tenantId);
        $total    = (clone $leads)->count();

        $stages = [
            ['stage' => 'Total Leads',    'count' => $total],
            ['stage' => 'Contacted',      'count' => (clone $leads)->whereIn('status', ['contacted', 'qualified', 'proposal', 'negotiation', 'won'])->count()],
            ['stage' => 'Qualified',      'count' => (clone $leads)->whereIn('status', ['qualified', 'proposal', 'negotiation', 'won'])->count()],
            ['stage' => 'Proposal',       'count' => (clone $leads)->whereIn('status', ['proposal', 'negotiation', 'won'])->count()],
            ['stage' => 'Negotiation',    'count' => (clone $leads)->whereIn('status', ['negotiation', 'won'])->count()],
            ['stage' => 'Won',            'count' => (clone $leads)->where('status', 'won')->count()],
        ];

        return collect($stages)->map(function ($stage) use ($total) {
            $stage['rate'] = $total > 0 ? round(($stage['count'] / $total) * 100, 1) : 0;
            return $stage;
        })->toArray();
    }
}
