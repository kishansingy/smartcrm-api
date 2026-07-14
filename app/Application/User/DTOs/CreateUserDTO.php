<?php

namespace App\Application\User\DTOs;

class CreateUserDTO
{
    public function __construct(
        public readonly string  $name,
        public readonly string  $email,
        public readonly string  $password,
        public readonly string  $role,
        public readonly ?string $phone = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name:     $data['name'],
            email:    $data['email'],
            password: $data['password'],
            role:     $data['role'],
            phone:    $data['phone'] ?? null,
        );
    }
}
