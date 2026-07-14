<?php

namespace App\Domain\Task\Contracts;

use App\Domain\Task\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TaskRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator;
    public function findById(int $id): ?Task;
    public function create(array $data): Task;
    public function update(Task $task, array $data): Task;
    public function delete(Task $task): bool;
    public function getUpcoming(int $userId, int $days = 7): Collection;
    public function getOverdue(int $tenantId): Collection;
    public function getStatsByUser(int $userId): array;
    public function getStatsByTenant(int $tenantId): array;
}
