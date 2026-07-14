<?php

namespace App\Application\Contact\DTOs;

class CreateContactNoteDTO
{
    public function __construct(
        public readonly string  $content,
        public readonly string  $type,
        public readonly ?array  $meta,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            content: $data['content'],
            type:    $data['type'] ?? 'note',
            meta:    $data['meta'] ?? null,
        );
    }
}
