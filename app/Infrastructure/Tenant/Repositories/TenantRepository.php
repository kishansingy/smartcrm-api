<?php

namespace App\Infrastructure\Tenant\Repositories;

use App\Domain\Tenant\Contracts\TenantRepositoryInterface;
use App\Domain\Tenant\Models\Tenant;

class TenantRepository implements TenantRepositoryInterface
{
    public function findById(int $id): ?Tenant
    {
        return Tenant::find($id);
    }

    public function findBySlug(string $slug): ?Tenant
    {
        return Tenant::where('slug', $slug)->first();
    }

    public function findByDomain(string $domain): ?Tenant
    {
        return Tenant::where('domain', $domain)->first();
    }

    public function create(array $data): Tenant
    {
        return Tenant::create($data);
    }

    public function update(Tenant $tenant, array $data): Tenant
    {
        $tenant->update($data);
        return $tenant->refresh();
    }

    public function delete(Tenant $tenant): bool
    {
        return $tenant->delete();
    }
}
