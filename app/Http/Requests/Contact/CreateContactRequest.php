<?php

namespace App\Http\Requests\Contact;

use App\Domain\Contact\Enums\ContactStatus;
use App\Domain\Contact\Enums\ContactType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('contacts.create');
    }

    public function rules(): array
    {
        return [
            'first_name'  => ['required', 'string', 'max:100'],
            'last_name'   => ['required', 'string', 'max:100'],
            'email'       => ['nullable', 'email', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:30'],
            'mobile'      => ['nullable', 'string', 'max:30'],
            'company'     => ['nullable', 'string', 'max:255'],
            'job_title'   => ['nullable', 'string', 'max:255'],
            'department'  => ['nullable', 'string', 'max:255'],
            'website'     => ['nullable', 'url', 'max:255'],
            'address'     => ['nullable', 'array'],
            'address.street'   => ['nullable', 'string', 'max:255'],
            'address.city'     => ['nullable', 'string', 'max:100'],
            'address.state'    => ['nullable', 'string', 'max:100'],
            'address.country'  => ['nullable', 'string', 'max:100'],
            'address.postal_code' => ['nullable', 'string', 'max:20'],
            'type'        => ['nullable', Rule::enum(ContactType::class)],
            'status'      => ['nullable', Rule::enum(ContactStatus::class)],
            'source'      => ['nullable', 'string', 'max:50'],
            'tags'        => ['nullable', 'array'],
            'tags.*'      => ['string', 'max:50'],
            'notes'       => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'meta'        => ['nullable', 'array'],
        ];
    }
}
