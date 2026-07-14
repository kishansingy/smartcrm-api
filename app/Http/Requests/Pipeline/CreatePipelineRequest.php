<?php

namespace App\Http\Requests\Pipeline;

use Illuminate\Foundation\Http\FormRequest;

class CreatePipelineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pipeline.create');
    }

    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:255'],
            'description'        => ['nullable', 'string'],
            'currency'           => ['nullable', 'string', 'size:3'],
            'is_default'         => ['nullable', 'boolean'],
            'stages'             => ['nullable', 'array'],
            'stages.*.name'        => ['required', 'string', 'max:100'],
            'stages.*.color'       => ['nullable', 'string', 'max:20'],
            'stages.*.position'    => ['nullable', 'integer', 'min:1'],
            'stages.*.probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'stages.*.is_won'      => ['nullable', 'boolean'],
            'stages.*.is_lost'     => ['nullable', 'boolean'],
        ];
    }
}
