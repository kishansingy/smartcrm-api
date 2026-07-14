<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;

class CreateContactNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('contacts.update');
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string'],
            'type'    => ['nullable', 'string', 'in:note,call,email,whatsapp,meeting'],
            'meta'    => ['nullable', 'array'],
        ];
    }
}
