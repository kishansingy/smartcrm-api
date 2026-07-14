<?php

namespace App\Http\Requests\Pipeline;

use Illuminate\Foundation\Http\FormRequest;

class CreateDealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pipeline.create');
    }

    public function rules(): array
    {
        return [
            'title'               => ['required', 'string', 'max:255'],
            'pipeline_id'         => ['required', 'integer', 'exists:pipelines,id'],
            'stage_id'            => ['required', 'integer', 'exists:pipeline_stages,id'],
            'lead_id'             => ['nullable', 'integer', 'exists:leads,id'],
            'contact_id'          => ['nullable', 'integer', 'exists:contacts,id'],
            'assigned_to'         => ['nullable', 'integer', 'exists:users,id'],
            'value'               => ['nullable', 'numeric', 'min:0'],
            'currency'            => ['nullable', 'string', 'size:3'],
            'probability'         => ['nullable', 'integer', 'min:0', 'max:100'],
            'expected_close_date' => ['nullable', 'date'],
            'notes'               => ['nullable', 'string'],
            'meta'                => ['nullable', 'array'],
        ];
    }
}
