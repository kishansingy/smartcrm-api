<?php

namespace App\Application\Lead\DTOs;

class LeadFilterDTO
{
    public function __construct(
        public readonly ?string $search,
        public readonly ?string $status,
        public readonly ?string $source,
        public readonly ?string $priority,
        public readonly ?int    $assignedTo,
        public readonly ?string $dateFrom,
        public readonly ?string $dateTo,
        public readonly string  $sortBy,
        public readonly string  $sortDir,
        public readonly int     $perPage,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            search:     $data['search']      ?? null,
            status:     $data['status']      ?? null,
            source:     $data['source']      ?? null,
            priority:   $data['priority']    ?? null,
            assignedTo: $data['assigned_to'] ?? null,
            dateFrom:   $data['date_from']   ?? null,
            dateTo:     $data['date_to']     ?? null,
            sortBy:     $data['sort_by']     ?? 'created_at',
            sortDir:    $data['sort_dir']    ?? 'desc',
            perPage:    (int) ($data['per_page'] ?? 20),
        );
    }
}
