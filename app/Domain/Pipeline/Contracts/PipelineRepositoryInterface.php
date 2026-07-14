<?php

namespace App\Domain\Pipeline\Contracts;

use App\Domain\Pipeline\Models\Pipeline;
use Illuminate\Support\Collection;

interface PipelineRepositoryInterface
{
    public function allForTenant(int $tenantId): Collection;
    public function findById(int $id): ?Pipeline;
    public function create(array $data): Pipeline;
    public function update(Pipeline $pipeline, array $data): Pipeline;
    public function delete(Pipeline $pipeline): bool;
    public function reorderStages(Pipeline $pipeline, array $stageOrder): void;
}
