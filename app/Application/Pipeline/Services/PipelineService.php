<?php

namespace App\Application\Pipeline\Services;

use App\Application\Pipeline\DTOs\CreatePipelineDTO;
use App\Domain\Pipeline\Contracts\PipelineRepositoryInterface;
use App\Domain\Pipeline\Models\Pipeline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class PipelineService
{
    public function __construct(
        private readonly PipelineRepositoryInterface $pipelineRepository,
    ) {}

    public function listForTenant(): Collection
    {
        return $this->pipelineRepository->allForTenant(Auth::user()->tenant_id);
    }

    public function create(CreatePipelineDTO $dto): Pipeline
    {
        // If this is set as default, unset others
        if ($dto->isDefault) {
            Pipeline::where('tenant_id', Auth::user()->tenant_id)
                ->update(['is_default' => false]);
        }

        $pipeline = $this->pipelineRepository->create([
            'tenant_id'   => Auth::user()->tenant_id,
            'name'        => $dto->name,
            'description' => $dto->description,
            'currency'    => $dto->currency,
            'is_default'  => $dto->isDefault,
            'is_active'   => true,
        ]);

        // Create stages
        foreach ($dto->stages as $index => $stage) {
            $pipeline->stages()->create([
                'name'        => $stage['name'],
                'color'       => $stage['color']       ?? '#6366f1',
                'position'    => $stage['position']    ?? ($index + 1),
                'probability' => $stage['probability'] ?? 20,
                'is_won'      => $stage['is_won']      ?? false,
                'is_lost'     => $stage['is_lost']     ?? false,
            ]);
        }

        return $pipeline->load('stages');
    }

    public function update(Pipeline $pipeline, array $data): Pipeline
    {
        if (($data['is_default'] ?? false)) {
            Pipeline::where('tenant_id', $pipeline->tenant_id)
                ->where('id', '!=', $pipeline->id)
                ->update(['is_default' => false]);
        }

        return $this->pipelineRepository->update($pipeline, $data);
    }

    public function delete(Pipeline $pipeline): bool
    {
        return $this->pipelineRepository->delete($pipeline);
    }

    public function reorderStages(Pipeline $pipeline, array $stageOrder): void
    {
        $this->pipelineRepository->reorderStages($pipeline, $stageOrder);
    }
}
