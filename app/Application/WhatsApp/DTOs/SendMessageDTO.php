<?php

namespace App\Application\WhatsApp\DTOs;

class SendMessageDTO
{
    public function __construct(
        public readonly string  $to,
        public readonly string  $type,
        public readonly ?string $text,
        public readonly ?string $templateName,
        public readonly ?string $templateLanguage,
        public readonly ?array  $templateComponents,
        public readonly ?string $mediaUrl,
        public readonly ?int    $conversationId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            to:                 $data['to'],
            type:               $data['type'] ?? 'text',
            text:               $data['text'] ?? null,
            templateName:       $data['template_name'] ?? null,
            templateLanguage:   $data['template_language'] ?? 'en',
            templateComponents: $data['template_components'] ?? null,
            mediaUrl:           $data['media_url'] ?? null,
            conversationId:     $data['conversation_id'] ?? null,
        );
    }
}
