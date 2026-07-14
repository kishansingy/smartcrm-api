<?php

namespace App\Domain\Auth\Contracts;

use App\Models\User;

interface AuthRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function createUser(array $data): User;
    public function updatePassword(User $user, string $password): bool;
}
