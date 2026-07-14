<?php

namespace App\Http\Requests\Lead;

use App\Domain\Lead\Enums\LeadStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('leads.update');
    }

    public function rules(): array
    {
        return [
            'ids'         => ['required', 'array', 'min:1'],
            'ids.*'       => ['integer', 'exists:leads,id'],
            'assigned_to' => ['sometimes', 'integer', 'exists:users,id'],
            'status'      => ['sometimes', Rule::enum(LeadStatus::class)],
        ];
    }
}
