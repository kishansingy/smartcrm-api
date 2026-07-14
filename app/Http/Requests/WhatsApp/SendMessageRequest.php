<?php

namespace App\Http\Requests\WhatsApp;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('whatsapp.send');
    }

    public function rules(): array
    {
        return [
            'to'                   => ['required', 'string', 'max:20'],
            'type'                 => ['required', Rule::in(['text', 'template', 'image', 'audio', 'video', 'document'])],
            'text'                 => ['required_if:type,text', 'nullable', 'string', 'max:4096'],
            'template_name'        => ['required_if:type,template', 'nullable', 'string'],
            'template_language'    => ['nullable', 'string', 'max:10'],
            'template_components'  => ['nullable', 'array'],
            'media_url'            => ['required_if:type,image,audio,video,document', 'nullable', 'url'],
            'conversation_id'      => ['nullable', 'integer', 'exists:whatsapp_conversations,id'],
            'scheduled_at'         => ['nullable', 'date', 'after:now'],
        ];
    }
}
