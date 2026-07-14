<?php

namespace App\Application\User\Services;

use App\Application\User\DTOs\CreateUserDTO;
use App\Application\User\DTOs\UpdateUserDTO;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserService
{
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $tenantId = Auth::user()->tenant_id;

        $query = User::where('tenant_id', $tenantId)->with('roles');

        if ($filters['search'] ?? null) {
            $query->where(function ($q) use ($filters) {
                $q->where('name',  'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        if ($filters['role'] ?? null) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $filters['role']));
        }

        if ($filters['status'] ?? null) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(CreateUserDTO $dto): User
    {
        $user = User::create([
            'tenant_id' => Auth::user()->tenant_id,
            'name'      => $dto->name,
            'email'     => $dto->email,
            'password'  => Hash::make($dto->password),
            'phone'     => $dto->phone,
            'status'    => 'active',
        ]);

        $user->assignRole($dto->role);

        return $user->load('roles');
    }

    public function update(User $user, UpdateUserDTO $dto): User
    {
        $data = array_filter([
            'name'   => $dto->name,
            'email'  => $dto->email,
            'phone'  => $dto->phone,
            'status' => $dto->status,
        ], fn ($v) => $v !== null);

        if ($dto->password) {
            $data['password'] = Hash::make($dto->password);
        }

        $user->update($data);

        if ($dto->role) {
            $user->syncRoles([$dto->role]);
        }

        return $user->refresh()->load('roles');
    }

    public function delete(User $user): void
    {
        // Prevent self-deletion
        if ($user->id === Auth::id()) {
            throw new \RuntimeException('You cannot delete your own account.');
        }

        $user->delete();
    }

    public function roles(): array
    {
        return Role::select('id', 'name')
            ->whereNotIn('name', ['super_admin'])
            ->get()
            ->toArray();
    }
}
