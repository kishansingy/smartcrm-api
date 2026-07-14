<?php

namespace App\Infrastructure\Task\Repositories;

use App\Domain\Task\Contracts\TaskRepositoryInterface;
use App\Domain\Task\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Task::with([
            'assignedTo:id,name,email',
            'createdBy:id,name',
            'lead:id,first_name,last_name',
            'contact:id,first_name,last_name',
            'deal:id,title',
        ]);

        if ($filters['search']   ?? null) $query->where('title', 'like', "%{$filters['search']}%");
        if ($filters['type']     ?? null) $query->where('type',       $filters['type']);
        if ($filters['status']   ?? null) $query->where('status',     $filters['status']);
        if ($filters['priority'] ?? null) $query->where('priority',   $filters['priority']);
        if ($filters['assigned_to'] ?? null) $query->where('assigned_to', $filters['assigned_to']);
        if ($filters['lead_id']     ?? null) $query->where('lead_id',     $filters['lead_id']);
        if ($filters['contact_id']  ?? null) $query->where('contact_id',  $filters['contact_id']);
        if ($filters['deal_id']     ?? null) $query->where('deal_id',     $filters['deal_id']);
        if ($filters['due_date_from'] ?? null) $query->whereDate('due_date', '>=', $filters['due_date_from']);
        if ($filters['due_date_to']   ?? null) $query->whereDate('due_date', '<=', $filters['due_date_to']);

        if ($filters['overdue'] ?? null) {
            $query->where('status', '!=', 'completed')
                  ->whereNotNull('due_date')
                  ->whereDate('due_date', '<', now());
        }

        $allowed = ['due_date', 'created_at', 'priority', 'title', 'status'];
        $sortBy  = in_array($filters['sort_by'] ?? '', $allowed) ? $filters['sort_by'] : 'due_date';
        $sortDir = ($filters['sort_dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($sortBy, $sortDir)->paginate($perPage);
    }

    public function findById(int $id): ?Task
    {
        return Task::with(['assignedTo', 'createdBy', 'lead', 'contact', 'deal', 'comments.user'])->find($id);
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task->refresh();
    }

    public function delete(Task $task): bool
    {
        return $task->delete();
    }

    public function getUpcoming(int $userId, int $days = 7): Collection
    {
        return Task::with(['lead:id,first_name,last_name', 'contact:id,first_name,last_name', 'deal:id,title'])
            ->where('assigned_to', $userId)
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '>=', now())
            ->whereDate('due_date', '<=', now()->addDays($days))
            ->orderBy('due_date')
            ->get();
    }

    public function getOverdue(int $tenantId): Collection
    {
        return Task::with(['assignedTo:id,name', 'lead:id,first_name,last_name'])
            ->where('tenant_id', $tenantId)
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->orderBy('due_date')
            ->get();
    }

    public function getStatsByUser(int $userId): array
    {
        $q = Task::where('assigned_to', $userId);

        return [
            'total'       => (clone $q)->count(),
            'pending'     => (clone $q)->where('status', 'pending')->count(),
            'in_progress' => (clone $q)->where('status', 'in_progress')->count(),
            'completed'   => (clone $q)->where('status', 'completed')->count(),
            'overdue'     => (clone $q)->where('status', '!=', 'completed')
                                        ->whereDate('due_date', '<', now())->count(),
            'due_today'   => (clone $q)->where('status', '!=', 'completed')
                                        ->whereDate('due_date', today())->count(),
        ];
    }

    public function getStatsByTenant(int $tenantId): array
    {
        $q = Task::where('tenant_id', $tenantId);

        return [
            'total'       => (clone $q)->count(),
            'pending'     => (clone $q)->where('status', 'pending')->count(),
            'in_progress' => (clone $q)->where('status', 'in_progress')->count(),
            'completed'   => (clone $q)->where('status', 'completed')->count(),
            'overdue'     => (clone $q)->where('status', '!=', 'completed')
                                        ->whereDate('due_date', '<', now())->count(),
            'due_today'   => (clone $q)->where('status', '!=', 'completed')
                                        ->whereDate('due_date', today())->count(),
            'this_week'   => (clone $q)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];
    }
}
