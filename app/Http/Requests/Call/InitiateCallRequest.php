<?php

namespace App\Http\Requests\Call;

use Illuminate\Foundation\Http\FormRequest;

class InitiateCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('calls.make');
    }

    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'string', 'max:30'],
            'contact_id'   => ['nullable', 'integer', 'exists:contacts,id'],
            'lead_id'      => ['nullable', 'integer', 'exists:leads,id'],
            'direction'    => ['nullable', 'in:outbound,inbound'],
            'notes'        => ['nullable', 'string', 'max:2000'],
            'provider'     => ['nullable', 'in:retell,exotel'],
            'agent_id'     => ['nullable', 'string'],
            'call_purpose' => ['nullable', 'string', 'max:100'],
        ];
    }
}
