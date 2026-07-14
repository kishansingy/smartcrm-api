<?php

namespace App\Application\Auth\Services;

use App\Application\Auth\DTOs\LoginDTO;
use App\Application\Auth\DTOs\RegisterDTO;
use App\Application\Tenant\Services\TenantService;
use App\Application\Tenant\DTOs\CreateTenantDTO;
use App\Domain\Auth\Contracts\AuthRepositoryInterface;
use App\Domain\Auth\Events\UserLoggedIn;
use App\Domain\Auth\Events\UserRegistered;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly AuthRepositoryInterface $authRepository,
        private readonly TenantService $tenantService,
    ) {}

    public function register(RegisterDTO $dto): array
    {
        return DB::transaction(function () use ($dto) {
            $tenant = $this->tenantService->create(new CreateTenantDTO(
                name: $dto->tenantName,
                slug: $dto->tenantSlug,
            ));

            $user = $this->authRepository->createUser([
                'name'      => $dto->name,
                'email'     => $dto->email,
                'password'  => Hash::make($dto->password),
                'tenant_id' => $tenant->id,
            ]);

            $user->assignRole('admin');

            event(new UserRegistered($user));

            $token = $user->createToken('auth_token')->plainTextToken;

            return compact('user', 'tenant', 'token');
        });
    }

    public function login(LoginDTO $dto): array
    {
        $user = $this->authRepository->findByEmail($dto->email);

        if (! $user || ! Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->tenant?->isActive()) {
            throw ValidationException::withMessages([
                'email' => ['Your account tenant is inactive.'],
            ]);
        }

        event(new UserLoggedIn($user));

        $token = $user->createToken($dto->deviceName)->plainTextToken;

        return compact('user', 'token');
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function me(User $user): User
    {
        return $user->load('tenant', 'roles', 'permissions');
    }
}
