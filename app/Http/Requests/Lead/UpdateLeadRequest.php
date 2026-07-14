<?php

namespace App\Http\Requests\Lead;

use App\Domain\Lead\Enums\LeadPriority;
use App\Domain\Lead\Enums\LeadSource;
use App\Domain\Lead\Enums\LeadStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('leads.update');
    }

    public function rules(): array
    {
        return [
            'first_name'  => ['sometimes', 'string', 'max:100'],
            'last_name'   => ['sometimes', 'string', 'max:100'],
            'email'       => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone'       => ['sometimes', 'nullable', 'string', 'max:30'],
            'company'     => ['sometimes', 'nullable', 'string', 'max:255'],
            'job_title'   => ['sometimes', 'nullable', 'string', 'max:255'],
            'source'      => ['sometimes', Rule::enum(LeadSource::class)],
            'status'      => ['sometimes', Rule::enum(LeadStatus::class)],
            'priority'    => ['sometimes', Rule::enum(LeadPriority::class)],
            'assigned_to' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'notes'       => ['sometimes', 'nullable', 'string'],
            'meta'        => ['sometimes', 'nullable', 'array'],
        ];
    }
}
