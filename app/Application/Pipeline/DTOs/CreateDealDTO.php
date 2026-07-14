<?php

namespace App\Application\Pipeline\DTOs;

class CreateDealDTO
{
    public function __construct(
        public readonly string  $title,
        public readonly int     $pipelineId,
        public readonly int     $stageId,
        public readonly ?int    $leadId,
        public readonly ?int    $contactId,
        public readonly ?int    $assignedTo,
        public readonly float   $value,
        public readonly string  $currency,
        public readonly int     $probability,
        public readonly ?string $expectedCloseDate,
        public readonly ?string $notes,
        public readonly ?array  $meta,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title:             $data['title'],
            pipelineId:        $data['pipeline_id'],
            stageId:           $data['stage_id'],
            leadId:            $data['lead_id']             ?? null,
            contactId:         $data['contact_id']          ?? null,
            assignedTo:        $data['assigned_to']         ?? null,
            value:             (float) ($data['value']      ?? 0),
            currency:          $data['currency']            ?? 'USD',
            probability:       (int) ($data['probability']  ?? 20),
            expectedCloseDate: $data['expected_close_date'] ?? null,
            notes:             $data['notes']               ?? null,
            meta:              $data['meta']                ?? null,
        );
    }
}
