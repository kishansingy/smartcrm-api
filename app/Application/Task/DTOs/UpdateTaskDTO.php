<?php

namespace App\Application\Task\DTOs;

class UpdateTaskDTO
{
    public function __construct(
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly ?string $type,
        public readonly ?string $priority,
        public readonly ?string $status,
        public readonly ?int    $assignedTo,
        public readonly ?int    $leadId,
        public readonly ?int    $contactId,
        public readonly ?int    $dealId,
        public readonly ?string $dueDate,
        public readonly ?string $dueTime,
        public readonly ?string $reminderAt,
        public readonly ?array  $meta,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title:       $data['title']       ?? null,
            description: $data['description'] ?? null,
            type:        $data['type']        ?? null,
            priority:    $data['priority']    ?? null,
            status:      $data['status']      ?? null,
            assignedTo:  $data['assigned_to'] ?? null,
            leadId:      $data['lead_id']     ?? null,
            contactId:   $data['contact_id']  ?? null,
            dealId:      $data['deal_id']     ?? null,
            dueDate:     $data['due_date']    ?? null,
            dueTime:     $data['due_time']    ?? null,
            reminderAt:  $data['reminder_at'] ?? null,
            meta:        $data['meta']        ?? null,
        );
    }
}
