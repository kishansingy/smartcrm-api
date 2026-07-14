<?php

namespace App\Application\Task\DTOs;

class TaskFilterDTO
{
    public function __construct(
        public readonly ?string $search,
        public readonly ?string $type,
        public readonly ?string $status,
        public readonly ?string $priority,
        public readonly ?int    $assignedTo,
        public readonly ?int    $leadId,
        public readonly ?int    $contactId,
        public readonly ?int    $dealId,
        public readonly ?string $dueDateFrom,
        public readonly ?string $dueDateTo,
        public readonly ?bool   $overdue,
        public readonly string  $sortBy,
        public readonly string  $sortDir,
        public readonly int     $perPage,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            search:      $data['search']        ?? null,
            type:        $data['type']          ?? null,
            status:      $data['status']        ?? null,
            priority:    $data['priority']      ?? null,
            assignedTo:  isset($data['assigned_to']) ? (int) $data['assigned_to'] : null,
            leadId:      isset($data['lead_id'])     ? (int) $data['lead_id']     : null,
            contactId:   isset($data['contact_id'])  ? (int) $data['contact_id']  : null,
            dealId:      isset($data['deal_id'])     ? (int) $data['deal_id']     : null,
            dueDateFrom: $data['due_date_from']  ?? null,
            dueDateTo:   $data['due_date_to']    ?? null,
            overdue:     isset($data['overdue']) ? (bool) $data['overdue'] : null,
            sortBy:      $data['sort_by']        ?? 'due_date',
            sortDir:     $data['sort_dir']       ?? 'asc',
            perPage:     (int) ($data['per_page'] ?? 20),
        );
    }
}
