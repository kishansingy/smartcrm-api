<?php

namespace App\Application\Call\DTOs;

class UpdateCallDTO
{
    public function __construct(
        public readonly ?string $status,
        public readonly ?int    $duration,
        public readonly ?string $recordingUrl,
        public readonly ?string $transcript,
        public readonly ?string $providerCallId,
        public readonly ?string $startedAt,
        public readonly ?string $endedAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status:         $data['status'] ?? null,
            duration:       $data['duration'] ?? null,
            recordingUrl:   $data['recording_url'] ?? null,
            transcript:     $data['transcript'] ?? null,
            providerCallId: $data['provider_call_id'] ?? null,
            startedAt:      $data['started_at'] ?? null,
            endedAt:        $data['ended_at'] ?? null,
        );
    }
}
