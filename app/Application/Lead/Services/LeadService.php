<?php

namespace App\Application\Lead\Services;

use App\Application\Lead\DTOs\CreateLeadDTO;
use App\Application\Lead\DTOs\LeadFilterDTO;
use App\Application\Lead\DTOs\UpdateLeadDTO;
use App\Domain\Lead\Contracts\LeadRepositoryInterface;
use App\Domain\Lead\Events\LeadAssigned;
use App\Domain\Lead\Events\LeadCreated;
use App\Domain\Lead\Events\LeadStatusChanged;
use App\Domain\Lead\Models\Lead;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class LeadService
{
    public function __construct(
        private readonly LeadRepositoryInterface $leadRepository,
    ) {}

    public function list(LeadFilterDTO $dto): LengthAwarePaginator
    {
        return $this->leadRepository->paginate([
            'search'      => $dto->search,
            'status'      => $dto->status,
            'source'      => $dto->source,
            'priority'    => $dto->priority,
            'assigned_to' => $dto->assignedTo,
            'date_from'   => $dto->dateFrom,
            'date_to'     => $dto->dateTo,
            'sort_by'     => $dto->sortBy,
            'sort_dir'    => $dto->sortDir,
        ], $dto->perPage);
    }

    public function create(CreateLeadDTO $dto): Lead
    {
        $lead = $this->leadRepository->create([
            'tenant_id'   => Auth::user()->tenant_id,
            'first_name'  => $dto->firstName,
            'last_name'   => $dto->lastName,
            'email'       => $dto->email,
            'phone'       => $dto->phone,
            'company'     => $dto->company,
            'job_title'   => $dto->jobTitle,
            'source'      => $dto->source,
            'status'      => $dto->status,
            'priority'    => $dto->priority,
            'assigned_to' => $dto->assignedTo ?? Auth::id(),
            'notes'       => $dto->notes,
            'meta'        => $dto->meta,
        ]);

        event(new LeadCreated($lead));

        return $lead;
    }

    public function update(Lead $lead, UpdateLeadDTO $dto): Lead
    {
        $oldStatus    = $lead->status;
        $previousUser = $lead->assigned_to;

        $data = array_filter([
            'first_name'  => $dto->firstName,
            'last_name'   => $dto->lastName,
            'email'       => $dto->email,
            'phone'       => $dto->phone,
            'company'     => $dto->company,
            'job_title'   => $dto->jobTitle,
            'source'      => $dto->source,
            'status'      => $dto->status,
            'priority'    => $dto->priority,
            'assigned_to' => $dto->assignedTo,
            'notes'       => $dto->notes,
            'meta'        => $dto->meta,
        ], fn ($v) => $v !== null);

        $updated = $this->leadRepository->update($lead, $data);

        if ($dto->status && $dto->status !== $oldStatus) {
            event(new LeadStatusChanged($updated, $oldStatus, $dto->status));
        }

        if ($dto->assignedTo && $dto->assignedTo !== $previousUser) {
            event(new LeadAssigned($updated, $previousUser, $dto->assignedTo));
        }

        return $updated;
    }

    public function delete(Lead $lead): bool
    {
        return $this->leadRepository->delete($lead);
    }

    public function stats(): array
    {
        return $this->leadRepository->getStatsByTenant(Auth::user()->tenant_id);
    }

    public function bulkAssign(array $ids, int $userId): int
    {
        return $this->leadRepository->bulkAssign($ids, $userId);
    }

    public function bulkUpdateStatus(array $ids, string $status): int
    {
        return $this->leadRepository->bulkUpdateStatus($ids, $status);
    }
}
