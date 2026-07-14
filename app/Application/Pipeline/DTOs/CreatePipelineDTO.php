<?php

namespace App\Application\Pipeline\DTOs;

class CreatePipelineDTO
{
    public function __construct(
        public readonly string  $name,
        public readonly ?string $description,
        public readonly string  $currency,
        public readonly bool    $isDefault,
        public readonly array   $stages,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name:        $data['name'],
            description: $data['description'] ?? null,
            currency:    $data['currency']    ?? 'USD',
            isDefault:   $data['is_default']  ?? false,
            stages:      $data['stages']      ?? [],
        );
    }
}
