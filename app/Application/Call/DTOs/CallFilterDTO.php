<?php

namespace App\Application\Call\DTOs;

class CallFilterDTO
{
    public function __construct(
        public readonly ?string $search,
        public readonly ?string $status,
        public readonly ?string $direction,
        public readonly ?int    $userId,
        public readonly ?int    $contactId,
        public readonly ?int    $leadId,
        public readonly ?string $dateFrom,
        public readonly ?string $dateTo,
        public readonly int     $perPage = 20,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            search:    $data['search']     ?? null,
            status:    $data['status']     ?? null,
            direction: $data['direction']  ?? null,
            userId:    isset($data['user_id'])    ? (int) $data['user_id']    : null,
            contactId: isset($data['contact_id']) ? (int) $data['contact_id'] : null,
            leadId:    isset($data['lead_id'])    ? (int) $data['lead_id']    : null,
            dateFrom:  $data['date_from']  ?? null,
            dateTo:    $data['date_to']    ?? null,
            perPage:   isset($data['per_page'])   ? (int) $data['per_page']   : 20,
        );
    }
}
