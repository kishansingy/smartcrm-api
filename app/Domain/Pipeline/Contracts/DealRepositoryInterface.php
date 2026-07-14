<?php

namespace App\Domain\Pipeline\Contracts;

use App\Domain\Pipeline\Models\Deal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DealRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator;
    public function findById(int $id): ?Deal;
    public function create(array $data): Deal;
    public function update(Deal $deal, array $data): Deal;
    public function delete(Deal $deal): bool;
    public function moveToStage(Deal $deal, int $stageId): Deal;
    public function getStatsByTenant(int $tenantId): array;
    public function getKanbanByPipeline(int $pipelineId): array;
}
