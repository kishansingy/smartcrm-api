<?php

namespace App\Domain\Call\Contracts;

use App\Domain\Call\Models\CallLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CallRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator;
    public function findById(int $id): ?CallLog;
    public function findByProviderCallId(string $providerCallId): ?CallLog;
    public function create(array $data): CallLog;
    public function update(CallLog $call, array $data): CallLog;
    public function getStatsByTenant(int $tenantId): array;
    public function getReportData(int $tenantId, array $filters): array;
}
