<?php

namespace App\Application\Pipeline\DTOs;

class DealFilterDTO
{
    public function __construct(
        public readonly ?int    $pipelineId,
        public readonly ?int    $stageId,
        public readonly ?string $status,
        public readonly ?int    $assignedTo,
        public readonly ?string $search,
        public readonly ?string $dateFrom,
        public readonly ?string $dateTo,
        public readonly string  $sortBy,
        public readonly string  $sortDir,
        public readonly int     $perPage,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            pipelineId: isset($data['pipeline_id']) ? (int) $data['pipeline_id'] : null,
            stageId:    isset($data['stage_id'])    ? (int) $data['stage_id']    : null,
            status:     $data['status']      ?? null,
            assignedTo: isset($data['assigned_to']) ? (int) $data['assigned_to'] : null,
            search:     $data['search']      ?? null,
            dateFrom:   $data['date_from']   ?? null,
            dateTo:     $data['date_to']     ?? null,
            sortBy:     $data['sort_by']     ?? 'created_at',
            sortDir:    $data['sort_dir']    ?? 'desc',
            perPage:    (int) ($data['per_page'] ?? 20),
        );
    }
}
