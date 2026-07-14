<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Application\Auth\DTOs\LoginDTO;
use App\Application\Auth\DTOs\RegisterDTO;
use App\Application\Auth\Services\AuthService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register(
            RegisterDTO::fromArray($request->validated())
        );

        return response()->json([
            'message' => 'Registration successful.',
            'token'   => $result['token'],
            'user'    => new UserResource($result['user']->load('tenant', 'roles')),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            LoginDTO::fromArray($request->validated())
        );

        return response()->json([
            'message' => 'Login successful.',
            'token'   => $result['token'],
            'user'    => new UserResource($result['user']->load('tenant', 'roles')),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $this->authService->me($request->user());

        return response()->json(['user' => new UserResource($user)]);
    }
}
