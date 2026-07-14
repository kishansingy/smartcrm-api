<?php

namespace App\Domain\Tenant\Contracts;

use App\Domain\Tenant\Models\Tenant;

interface TenantRepositoryInterface
{
    public function findById(int $id): ?Tenant;
    public function findBySlug(string $slug): ?Tenant;
    public function findByDomain(string $domain): ?Tenant;
    public function create(array $data): Tenant;
    public function update(Tenant $tenant, array $data): Tenant;
    public function delete(Tenant $tenant): bool;
}
