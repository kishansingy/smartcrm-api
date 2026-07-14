<?php

namespace App\Application\User\DTOs;

class UpdateUserDTO
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $email,
        public readonly ?string $password,
        public readonly ?string $role,
        public readonly ?string $phone,
        public readonly ?string $status,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name:     $data['name']     ?? null,
            email:    $data['email']    ?? null,
            password: $data['password'] ?? null,
            role:     $data['role']     ?? null,
            phone:    $data['phone']    ?? null,
            status:   $data['status']   ?? null,
        );
    }
}
