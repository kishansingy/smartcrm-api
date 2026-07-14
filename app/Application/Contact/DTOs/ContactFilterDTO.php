<?php

namespace App\Application\Contact\DTOs;

class ContactFilterDTO
{
    public function __construct(
        public readonly ?string $search,
        public readonly ?string $type,
        public readonly ?string $status,
        public readonly ?string $source,
        public readonly ?int    $assignedTo,
        public readonly ?string $tag,
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
            type:       $data['type']        ?? null,
            status:     $data['status']      ?? null,
            source:     $data['source']      ?? null,
            assignedTo: $data['assigned_to'] ?? null,
            tag:        $data['tag']         ?? null,
            dateFrom:   $data['date_from']   ?? null,
            dateTo:     $data['date_to']     ?? null,
            sortBy:     $data['sort_by']     ?? 'created_at',
            sortDir:    $data['sort_dir']    ?? 'desc',
            perPage:    (int) ($data['per_page'] ?? 20),
        );
    }
}
