<?php

namespace App\Http\Requests\Pipeline;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pipeline.update');
    }

    public function rules(): array
    {
        return [
            'title'               => ['sometimes', 'string', 'max:255'],
            'stage_id'            => ['sometimes', 'integer', 'exists:pipeline_stages,id'],
            'contact_id'          => ['sometimes', 'nullable', 'integer', 'exists:contacts,id'],
            'assigned_to'         => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'value'               => ['sometimes', 'numeric', 'min:0'],
            'currency'            => ['sometimes', 'string', 'size:3'],
            'probability'         => ['sometimes', 'integer', 'min:0', 'max:100'],
            'status'              => ['sometimes', Rule::in(['open', 'won', 'lost'])],
            'expected_close_date' => ['sometimes', 'nullable', 'date'],
            'lost_reason'         => ['sometimes', 'nullable', 'string'],
            'notes'               => ['sometimes', 'nullable', 'string'],
            'meta'                => ['sometimes', 'nullable', 'array'],
        ];
    }
}
