<?php

namespace App\Domain\Lead\Contracts;

use App\Domain\Lead\Models\Lead;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface LeadRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator;
    public function findById(int $id): ?Lead;
    public function create(array $data): Lead;
    public function update(Lead $lead, array $data): Lead;
    public function delete(Lead $lead): bool;
    public function bulkAssign(array $ids, int $userId): int;
    public function bulkUpdateStatus(array $ids, string $status): int;
    public function getStatsByTenant(int $tenantId): array;
}
