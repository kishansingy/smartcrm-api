<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Application\User\DTOs\CreateUserDTO;
use App\Application\User\DTOs\UpdateUserDTO;
use App\Application\User\Services\UserService;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('users.view');

        $paginated = $this->userService->list($request->query(), (int) $request->query('per_page', 20));

        return ApiResponse::paginated($paginated->through(
            fn ($user) => new UserResource($user)
        ));
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = $this->userService->create(
            CreateUserDTO::fromArray($request->validated())
        );

        return ApiResponse::success(new UserResource($user), 'User created.', 201);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('users.view');

        return ApiResponse::success(new UserResource($user->load('roles')));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $updated = $this->userService->update(
            $user,
            UpdateUserDTO::fromArray($request->validated())
        );

        return ApiResponse::success(new UserResource($updated), 'User updated.');
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('users.delete');

        try {
            $this->userService->delete($user);
            return ApiResponse::success(null, 'User deleted.');
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }
    }

    public function roles(): JsonResponse
    {
        $this->authorize('users.view');

        return ApiResponse::success($this->userService->roles());
    }
}
