<?php

namespace App\Application\Pipeline\Services;

use App\Application\Pipeline\DTOs\CreateDealDTO;
use App\Application\Pipeline\DTOs\DealFilterDTO;
use App\Application\Pipeline\DTOs\UpdateDealDTO;
use App\Domain\Pipeline\Contracts\DealRepositoryInterface;
use App\Domain\Pipeline\Events\DealCreated;
use App\Domain\Pipeline\Events\DealLost;
use App\Domain\Pipeline\Events\DealStageChanged;
use App\Domain\Pipeline\Events\DealWon;
use App\Domain\Pipeline\Models\Deal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class DealService
{
    public function __construct(
        private readonly DealRepositoryInterface $dealRepository,
    ) {}

    public function list(DealFilterDTO $dto): LengthAwarePaginator
    {
        return $this->dealRepository->paginate([
            'pipeline_id' => $dto->pipelineId,
            'stage_id'    => $dto->stageId,
            'status'      => $dto->status,
            'assigned_to' => $dto->assignedTo,
            'search'      => $dto->search,
            'date_from'   => $dto->dateFrom,
            'date_to'     => $dto->dateTo,
            'sort_by'     => $dto->sortBy,
            'sort_dir'    => $dto->sortDir,
        ], $dto->perPage);
    }

    public function create(CreateDealDTO $dto): Deal
    {
        $deal = $this->dealRepository->create([
            'tenant_id'           => Auth::user()->tenant_id,
            'pipeline_id'         => $dto->pipelineId,
            'stage_id'            => $dto->stageId,
            'lead_id'             => $dto->leadId,
            'contact_id'          => $dto->contactId,
            'assigned_to'         => $dto->assignedTo ?? Auth::id(),
            'title'               => $dto->title,
            'value'               => $dto->value,
            'currency'            => $dto->currency,
            'probability'         => $dto->probability,
            'status'              => 'open',
            'expected_close_date' => $dto->expectedCloseDate,
            'notes'               => $dto->notes,
            'meta'                => $dto->meta,
        ]);

        event(new DealCreated($deal));

        return $deal;
    }

    public function update(Deal $deal, UpdateDealDTO $dto): Deal
    {
        $oldStageId = $deal->stage_id;
        $oldStatus  = $deal->status;

        $data = array_filter([
            'title'               => $dto->title,
            'stage_id'            => $dto->stageId,
            'contact_id'          => $dto->contactId,
            'assigned_to'         => $dto->assignedTo,
            'value'               => $dto->value,
            'currency'            => $dto->currency,
            'probability'         => $dto->probability,
            'status'              => $dto->status,
            'expected_close_date' => $dto->expectedCloseDate,
            'lost_reason'         => $dto->lostReason,
            'notes'               => $dto->notes,
            'meta'                => $dto->meta,
        ], fn ($v) => $v !== null);

        // Set closed_at when won or lost
        if (isset($data['status']) && in_array($data['status'], ['won', 'lost']) && $oldStatus === 'open') {
            $data['closed_at'] = now();
        }

        $updated = $this->dealRepository->update($deal, $data);

        if ($dto->stageId && $dto->stageId !== $oldStageId) {
            event(new DealStageChanged($updated, $oldStageId, $dto->stageId));
        }

        if ($dto->status === 'won' && $oldStatus !== 'won') {
            event(new DealWon($updated));
        }

        if ($dto->status === 'lost' && $oldStatus !== 'lost') {
            event(new DealLost($updated, $dto->lostReason));
        }

        return $updated;
    }

    public function delete(Deal $deal): bool
    {
        return $this->dealRepository->delete($deal);
    }

    public function moveToStage(Deal $deal, int $stageId): Deal
    {
        $oldStageId = $deal->stage_id;
        $updated    = $this->dealRepository->moveToStage($deal, $stageId);

        event(new DealStageChanged($updated, $oldStageId, $stageId));

        return $updated;
    }

    public function stats(): array
    {
        return $this->dealRepository->getStatsByTenant(Auth::user()->tenant_id);
    }

    public function kanban(int $pipelineId): array
    {
        return $this->dealRepository->getKanbanByPipeline($pipelineId);
    }
}
