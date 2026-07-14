<?php

namespace App\Http\Requests\Task;

use App\Domain\Task\Enums\TaskPriority;
use App\Domain\Task\Enums\TaskType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('tasks.create');
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type'        => ['nullable', Rule::enum(TaskType::class)],
            'priority'    => ['nullable', Rule::enum(TaskPriority::class)],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'lead_id'     => ['nullable', 'integer', 'exists:leads,id'],
            'contact_id'  => ['nullable', 'integer', 'exists:contacts,id'],
            'deal_id'     => ['nullable', 'integer', 'exists:deals,id'],
            'due_date'    => ['nullable', 'date'],
            'due_time'    => ['nullable', 'date_format:H:i'],
            'reminder_at' => ['nullable', 'date'],
            'meta'        => ['nullable', 'array'],
        ];
    }
}
