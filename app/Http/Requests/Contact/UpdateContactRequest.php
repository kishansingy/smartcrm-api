<?php

namespace App\Http\Requests\Contact;

use App\Domain\Contact\Enums\ContactStatus;
use App\Domain\Contact\Enums\ContactType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('contacts.update');
    }

    public function rules(): array
    {
        return [
            'first_name'  => ['sometimes', 'string', 'max:100'],
            'last_name'   => ['sometimes', 'string', 'max:100'],
            'email'       => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone'       => ['sometimes', 'nullable', 'string', 'max:30'],
            'mobile'      => ['sometimes', 'nullable', 'string', 'max:30'],
            'company'     => ['sometimes', 'nullable', 'string', 'max:255'],
            'job_title'   => ['sometimes', 'nullable', 'string', 'max:255'],
            'department'  => ['sometimes', 'nullable', 'string', 'max:255'],
            'website'     => ['sometimes', 'nullable', 'url', 'max:255'],
            'address'     => ['sometimes', 'nullable', 'array'],
            'type'        => ['sometimes', Rule::enum(ContactType::class)],
            'status'      => ['sometimes', Rule::enum(ContactStatus::class)],
            'source'      => ['sometimes', 'nullable', 'string', 'max:50'],
            'tags'        => ['sometimes', 'nullable', 'array'],
            'tags.*'      => ['string', 'max:50'],
            'notes'       => ['sometimes', 'nullable', 'string'],
            'assigned_to' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'meta'        => ['sometimes', 'nullable', 'array'],
        ];
    }
}
