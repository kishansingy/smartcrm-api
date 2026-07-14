<?php

namespace App\Infrastructure\Pipeline\Repositories;

use App\Domain\Pipeline\Contracts\DealRepositoryInterface;
use App\Domain\Pipeline\Models\Deal;
use App\Domain\Pipeline\Models\PipelineStage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DealRepository implements DealRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Deal::with(['stage', 'pipeline', 'contact', 'assignedTo:id,name,email']);

        if ($filters['pipeline_id'] ?? null) $query->where('pipeline_id', $filters['pipeline_id']);
        if ($filters['stage_id']    ?? null) $query->where('stage_id',    $filters['stage_id']);
        if ($filters['status']      ?? null) $query->where('status',      $filters['status']);
        if ($filters['assigned_to'] ?? null) $query->where('assigned_to', $filters['assigned_to']);
        if ($filters['date_from']   ?? null) $query->whereDate('created_at', '>=', $filters['date_from']);
        if ($filters['date_to']     ?? null) $query->whereDate('created_at', '<=', $filters['date_to']);

        if ($filters['search'] ?? null) {
            $query->where('title', 'like', "%{$filters['search']}%");
        }

        $allowed = ['created_at', 'value', 'expected_close_date', 'title'];
        $sortBy  = in_array($filters['sort_by'] ?? '', $allowed) ? $filters['sort_by'] : 'created_at';
        $sortDir = ($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $sortDir)->paginate($perPage);
    }

    public function findById(int $id): ?Deal
    {
        return Deal::with(['stage', 'pipeline', 'contact', 'lead', 'assignedTo', 'activities.user'])->find($id);
    }

    public function create(array $data): Deal
    {
        return Deal::create($data);
    }

    public function update(Deal $deal, array $data): Deal
    {
        $deal->update($data);
        return $deal->refresh();
    }

    public function delete(Deal $deal): bool
    {
        return $deal->delete();
    }

    public function moveToStage(Deal $deal, int $stageId): Deal
    {
        $stage = PipelineStage::findOrFail($stageId);

        $data = ['stage_id' => $stageId, 'probability' => $stage->probability];

        if ($stage->is_won) {
            $data['status'] = 'won';
            $data['closed_at'] = now();
        } elseif ($stage->is_lost) {
            $data['status'] = 'lost';
            $data['closed_at'] = now();
        }

        $deal->update($data);
        return $deal->refresh();
    }

    public function getStatsByTenant(int $tenantId): array
    {
        $q = Deal::where('tenant_id', $tenantId);

        return [
            'total'        => (clone $q)->count(),
            'open'         => (clone $q)->where('status', 'open')->count(),
            'won'          => (clone $q)->where('status', 'won')->count(),
            'lost'         => (clone $q)->where('status', 'lost')->count(),
            'total_value'  => (clone $q)->where('status', 'open')->sum('value'),
            'won_value'    => (clone $q)->where('status', 'won')->sum('value'),
            'this_month'   => (clone $q)->whereMonth('created_at', now()->month)->count(),
            'won_month'    => (clone $q)->where('status', 'won')->whereMonth('closed_at', now()->month)->count(),
        ];
    }

    public function getKanbanByPipeline(int $pipelineId): array
    {
        $stages = PipelineStage::where('pipeline_id', $pipelineId)
            ->with(['deals' => fn ($q) => $q->with(['contact:id,first_name,last_name', 'assignedTo:id,name'])
                ->where('status', 'open')
                ->orderBy('created_at', 'desc')
            ])
            ->orderBy('position')
            ->get();

        return $stages->map(fn ($stage) => [
            'id'          => $stage->id,
            'name'        => $stage->name,
            'color'       => $stage->color,
            'position'    => $stage->position,
            'probability' => $stage->probability,
            'is_won'      => $stage->is_won,
            'is_lost'     => $stage->is_lost,
            'deals_count' => $stage->deals->count(),
            'deals_value' => $stage->deals->sum('value'),
            'deals'       => $stage->deals->values(),
        ])->toArray();
    }
}
