<?php

namespace App\Application\Contact\DTOs;

class UpdateContactDTO
{
    public function __construct(
        public readonly ?string $firstName,
        public readonly ?string $lastName,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $mobile,
        public readonly ?string $company,
        public readonly ?string $jobTitle,
        public readonly ?string $department,
        public readonly ?string $website,
        public readonly ?array  $address,
        public readonly ?string $type,
        public readonly ?string $status,
        public readonly ?string $source,
        public readonly ?array  $tags,
        public readonly ?string $notes,
        public readonly ?int    $assignedTo,
        public readonly ?array  $meta,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            firstName:  $data['first_name']  ?? null,
            lastName:   $data['last_name']   ?? null,
            email:      $data['email']       ?? null,
            phone:      $data['phone']       ?? null,
            mobile:     $data['mobile']      ?? null,
            company:    $data['company']     ?? null,
            jobTitle:   $data['job_title']   ?? null,
            department: $data['department']  ?? null,
            website:    $data['website']     ?? null,
            address:    $data['address']     ?? null,
            type:       $data['type']        ?? null,
            status:     $data['status']      ?? null,
            source:     $data['source']      ?? null,
            tags:       $data['tags']        ?? null,
            notes:      $data['notes']       ?? null,
            assignedTo: $data['assigned_to'] ?? null,
            meta:       $data['meta']        ?? null,
        );
    }
}
