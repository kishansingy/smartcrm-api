<?php

namespace App\Application\Tenant\Services;

use App\Application\Tenant\DTOs\CreateTenantDTO;
use App\Domain\Tenant\Contracts\TenantRepositoryInterface;
use App\Domain\Tenant\Events\TenantCreated;
use App\Domain\Tenant\Models\Tenant;

class TenantService
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
    ) {}

    public function create(CreateTenantDTO $dto): Tenant
    {
        $tenant = $this->tenantRepository->create([
            'name'          => $dto->name,
            'slug'          => $dto->slug,
            'domain'        => $dto->domain,
            'plan'          => $dto->plan,
            'status'        => 'active',
            'trial_ends_at' => now()->addDays(14),
        ]);

        event(new TenantCreated($tenant));

        return $tenant;
    }

    public function findById(int $id): ?Tenant
    {
        return $this->tenantRepository->findById($id);
    }

    public function findBySlug(string $slug): ?Tenant
    {
        return $this->tenantRepository->findBySlug($slug);
    }
}
