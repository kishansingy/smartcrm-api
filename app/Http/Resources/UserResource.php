<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'phone'         => $this->phone,
            'avatar'        => $this->avatar,
            'status'        => $this->status,
            'roles'         => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'permissions'   => $this->whenLoaded('permissions', fn () => $this->getAllPermissions()->pluck('name')),
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'created_at'    => $this->created_at?->toIso8601String(),
        ];
    }
}
