<?php

namespace App\Application\Tenant\DTOs;

class CreateTenantDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $domain = null,
        public readonly string $plan = 'trial',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name:   $data['name'],
            slug:   $data['slug'],
            domain: $data['domain'] ?? null,
            plan:   $data['plan']   ?? 'trial',
        );
    }
}
