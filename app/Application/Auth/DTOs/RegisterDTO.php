<?php

namespace App\Application\Auth\DTOs;

class RegisterDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly string $tenantName,
        public readonly string $tenantSlug,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name:       $data['name'],
            email:      $data['email'],
            password:   $data['password'],
            tenantName: $data['tenant_name'],
            tenantSlug: $data['tenant_slug'],
        );
    }
}
