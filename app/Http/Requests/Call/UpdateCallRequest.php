<?php

namespace App\Http\Requests\Call;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('calls.view');
    }

    public function rules(): array
    {
        return [
            'status'           => ['nullable', 'in:initiated,ringing,in_progress,completed,failed,no_answer,busy'],
            'duration'         => ['nullable', 'integer', 'min:0'],
            'recording_url'    => ['nullable', 'url', 'max:500'],
            'transcript'       => ['nullable', 'string'],
            'provider_call_id' => ['nullable', 'string', 'max:100'],
            'started_at'       => ['nullable', 'date'],
            'ended_at'         => ['nullable', 'date'],
        ];
    }
}
