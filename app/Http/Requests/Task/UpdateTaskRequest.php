<?php

namespace App\Http\Requests\Task;

use App\Domain\Task\Enums\TaskPriority;
use App\Domain\Task\Enums\TaskStatus;
use App\Domain\Task\Enums\TaskType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('tasks.update');
    }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'type'        => ['sometimes', Rule::enum(TaskType::class)],
            'priority'    => ['sometimes', Rule::enum(TaskPriority::class)],
            'status'      => ['sometimes', Rule::enum(TaskStatus::class)],
            'assigned_to' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'lead_id'     => ['sometimes', 'nullable', 'integer', 'exists:leads,id'],
            'contact_id'  => ['sometimes', 'nullable', 'integer', 'exists:contacts,id'],
            'deal_id'     => ['sometimes', 'nullable', 'integer', 'exists:deals,id'],
            'due_date'    => ['sometimes', 'nullable', 'date'],
            'due_time'    => ['sometimes', 'nullable', 'date_format:H:i'],
            'reminder_at' => ['sometimes', 'nullable', 'date'],
            'meta'        => ['sometimes', 'nullable', 'array'],
        ];
    }
}
