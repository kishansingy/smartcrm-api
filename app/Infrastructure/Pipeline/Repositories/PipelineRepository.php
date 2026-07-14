<?php

namespace App\Infrastructure\Pipeline\Repositories;

use App\Domain\Pipeline\Contracts\PipelineRepositoryInterface;
use App\Domain\Pipeline\Models\Pipeline;
use Illuminate\Support\Collection;

class PipelineRepository implements PipelineRepositoryInterface
{
    public function allForTenant(int $tenantId): Collection
    {
        return Pipeline::with('stages')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();
    }

    public function findById(int $id): ?Pipeline
    {
        return Pipeline::with(['stages', 'deals'])->find($id);
    }

    public function create(array $data): Pipeline
    {
        return Pipeline::create($data);
    }

    public function update(Pipeline $pipeline, array $data): Pipeline
    {
        $pipeline->update($data);
        return $pipeline->refresh()->load('stages');
    }

    public function delete(Pipeline $pipeline): bool
    {
        return $pipeline->delete();
    }

    public function reorderStages(Pipeline $pipeline, array $stageOrder): void
    {
        foreach ($stageOrder as $position => $stageId) {
            $pipeline->stages()->where('id', $stageId)->update(['position' => $position + 1]);
        }
    }
}
