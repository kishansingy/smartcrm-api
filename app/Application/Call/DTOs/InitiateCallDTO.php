<?php

namespace App\Application\Call\DTOs;

class InitiateCallDTO
{
    public function __construct(
        public readonly string  $phoneNumber,
        public readonly ?int    $contactId,
        public readonly ?int    $leadId,
        public readonly string  $direction  = 'outbound',
        public readonly ?string $notes      = null,
        public readonly string  $provider   = 'retell',
        public readonly ?string $agentId    = null,
        public readonly ?string $callPurpose = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            phoneNumber:  $data['phone_number'],
            contactId:    $data['contact_id'] ?? null,
            leadId:       $data['lead_id'] ?? null,
            direction:    $data['direction'] ?? 'outbound',
            notes:        $data['notes'] ?? null,
            provider:     $data['provider'] ?? 'retell',
            agentId:      $data['agent_id'] ?? null,
            callPurpose:  $data['call_purpose'] ?? null,
        );
    }
}
