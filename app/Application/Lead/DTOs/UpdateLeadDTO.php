<?php

namespace App\Application\Lead\DTOs;

class UpdateLeadDTO
{
    public function __construct(
        public readonly ?string $firstName,
        public readonly ?string $lastName,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $company,
        public readonly ?string $jobTitle,
        public readonly ?string $source,
        public readonly ?string $status,
        public readonly ?string $priority,
        public readonly ?int    $assignedTo,
        public readonly ?string $notes,
        public readonly ?array  $meta,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            firstName:  $data['first_name']  ?? null,
            lastName:   $data['last_name']   ?? null,
            email:      $data['email']       ?? null,
            phone:      $data['phone']       ?? null,
            company:    $data['company']     ?? null,
            jobTitle:   $data['job_title']   ?? null,
            source:     $data['source']      ?? null,
            status:     $data['status']      ?? null,
            priority:   $data['priority']    ?? null,
            assignedTo: $data['assigned_to'] ?? null,
            notes:      $data['notes']       ?? null,
            meta:       $data['meta']        ?? null,
        );
    }
}
