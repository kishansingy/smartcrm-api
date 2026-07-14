<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.update');
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name'     => ['nullable', 'string', 'max:100'],
            'email'    => ['nullable', 'email', "unique:users,email,{$userId}"],
            'password' => ['nullable', 'string', 'min:8'],
            'role'     => ['nullable', 'string', 'exists:roles,name'],
            'phone'    => ['nullable', 'string', 'max:30'],
            'status'   => ['nullable', 'in:active,inactive'],
        ];
    }
}
