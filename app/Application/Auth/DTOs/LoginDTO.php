<?php

namespace App\Application\Auth\DTOs;

class LoginDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly string $deviceName = 'web',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email:      $data['email'],
            password:   $data['password'],
            deviceName: $data['device_name'] ?? 'web',
        );
    }
}
