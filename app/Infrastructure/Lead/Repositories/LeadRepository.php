<?php

namespace App\Infrastructure\Lead\Repositories;

use App\Domain\Lead\Contracts\LeadRepositoryInterface;
use App\Domain\Lead\Models\Lead;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LeadRepository implements LeadRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Lead::with(['assignedTo:id,name,email', 'tenant:id,name']);

        if ($filters['search'] ?? null) {
            $query->where(function ($q) use ($filters) {
                $q->where('first_name', 'like', "%{$filters['search']}%")
                  ->orWhere('last_name',  'like', "%{$filters['search']}%")
                  ->orWhere('email',      'like', "%{$filters['search']}%")
                  ->orWhere('phone',      'like', "%{$filters['search']}%")
                  ->orWhere('company',    'like', "%{$filters['search']}%");
            });
        }

        if ($filters['status']   ?? null) $query->where('status',      $filters['status']);
        if ($filters['source']   ?? null) $query->where('source',      $filters['source']);
        if ($filters['priority'] ?? null) $query->where('priority',    $filters['priority']);
        if ($filters['assigned_to'] ?? null) $query->where('assigned_to', $filters['assigned_to']);
        if ($filters['date_from']   ?? null) $query->whereDate('created_at', '>=', $filters['date_from']);
        if ($filters['date_to']     ?? null) $query->whereDate('created_at', '<=', $filters['date_to']);

        $sortBy  = in_array($filters['sort_by'] ?? '', ['created_at', 'updated_at', 'first_name', 'score', 'priority'])
            ? $filters['sort_by']
            : 'created_at';

        $sortDir = ($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $sortDir)->paginate($perPage);
    }

    public function findById(int $id): ?Lead
    {
        return Lead::with(['assignedTo', 'activities.user'])->find($id);
    }

    public function create(array $data): Lead
    {
        return Lead::create($data);
    }

    public function update(Lead $lead, array $data): Lead
    {
        $lead->update($data);
        return $lead->refresh();
    }

    public function delete(Lead $lead): bool
    {
        return $lead->delete();
    }

    public function bulkAssign(array $ids, int $userId): int
    {
        return Lead::whereIn('id', $ids)->update(['assigned_to' => $userId]);
    }

    public function bulkUpdateStatus(array $ids, string $status): int
    {
        return Lead::whereIn('id', $ids)->update(['status' => $status]);
    }

    public function getStatsByTenant(int $tenantId): array
    {
        $leads = Lead::where('tenant_id', $tenantId);

        return [
            'total'       => (clone $leads)->count(),
            'new'         => (clone $leads)->where('status', 'new')->count(),
            'contacted'   => (clone $leads)->where('status', 'contacted')->count(),
            'qualified'   => (clone $leads)->where('status', 'qualified')->count(),
            'won'         => (clone $leads)->where('status', 'won')->count(),
            'lost'        => (clone $leads)->where('status', 'lost')->count(),
            'this_month'  => (clone $leads)->whereMonth('created_at', now()->month)->count(),
            'this_week'   => (clone $leads)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];
    }
}
