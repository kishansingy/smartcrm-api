<?php

namespace App\Application\Pipeline\DTOs;

class UpdateDealDTO
{
    public function __construct(
        public readonly ?string $title,
        public readonly ?int    $stageId,
        public readonly ?int    $contactId,
        public readonly ?int    $assignedTo,
        public readonly ?float  $value,
        public readonly ?string $currency,
        public readonly ?int    $probability,
        public readonly ?string $status,
        public readonly ?string $expectedCloseDate,
        public readonly ?string $lostReason,
        public readonly ?string $notes,
        public readonly ?array  $meta,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title:             $data['title']               ?? null,
            stageId:           $data['stage_id']            ?? null,
            contactId:         $data['contact_id']          ?? null,
            assignedTo:        $data['assigned_to']         ?? null,
            value:             isset($data['value']) ? (float) $data['value'] : null,
            currency:          $data['currency']            ?? null,
            probability:       isset($data['probability']) ? (int) $data['probability'] : null,
            status:            $data['status']              ?? null,
            expectedCloseDate: $data['expected_close_date'] ?? null,
            lostReason:        $data['lost_reason']         ?? null,
            notes:             $data['notes']               ?? null,
            meta:              $data['meta']                ?? null,
        );
    }
}
