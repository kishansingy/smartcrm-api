<?php

namespace App\Infrastructure\Auth\Repositories;

use App\Domain\Auth\Contracts\AuthRepositoryInterface;
use App\Models\User;

class AuthRepository implements AuthRepositoryInterface
{
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function createUser(array $data): User
    {
        return User::create($data);
    }

    public function updatePassword(User $user, string $password): bool
    {
        return $user->update(['password' => bcrypt($password)]);
    }
}
