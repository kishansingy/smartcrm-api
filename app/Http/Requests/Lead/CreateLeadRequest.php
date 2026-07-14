<?php

namespace App\Http\Requests\Lead;

use App\Domain\Lead\Enums\LeadPriority;
use App\Domain\Lead\Enums\LeadSource;
use App\Domain\Lead\Enums\LeadStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('leads.create');
    }

    public function rules(): array
    {
        return [
            'first_name'  => ['required', 'string', 'max:100'],
            'last_name'   => ['required', 'string', 'max:100'],
            'email'       => ['nullable', 'email', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:30'],
            'company'     => ['nullable', 'string', 'max:255'],
            'job_title'   => ['nullable', 'string', 'max:255'],
            'source'      => ['nullable', Rule::enum(LeadSource::class)],
            'status'      => ['nullable', Rule::enum(LeadStatus::class)],
            'priority'    => ['nullable', Rule::enum(LeadPriority::class)],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'notes'       => ['nullable', 'string'],
            'meta'        => ['nullable', 'array'],
        ];
    }
}
