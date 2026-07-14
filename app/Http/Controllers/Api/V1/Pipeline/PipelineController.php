<?php

namespace App\Http\Controllers\Api\V1\Pipeline;

use App\Application\Pipeline\DTOs\CreatePipelineDTO;
use App\Application\Pipeline\Services\PipelineService;
use App\Domain\Pipeline\Models\Pipeline;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pipeline\CreatePipelineRequest;
use App\Http\Resources\PipelineResource;
use App\Support\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PipelineController extends Controller
{
    public function __construct(private readonly PipelineService $pipelineService) {}

    public function index(): JsonResponse
    {
        $this->authorize('pipeline.view');

        $pipelines = $this->pipelineService->listForTenant();

        return ApiResponse::success(PipelineResource::collection($pipelines));
    }

    public function store(CreatePipelineRequest $request): JsonResponse
    {
        $pipeline = $this->pipelineService->create(
            CreatePipelineDTO::fromArray($request->validated())
        );

        return ApiResponse::success(new PipelineResource($pipeline), 'Pipeline created.', 201);
    }

    public function show(Pipeline $pipeline): JsonResponse
    {
        $this->authorize('pipeline.view');

        return ApiResponse::success(
            new PipelineResource($pipeline->load('stages'))
        );
    }

    public function update(Request $request, Pipeline $pipeline): JsonResponse
    {
        $this->authorize('pipeline.update');

        $request->validate([
            'name'        => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'currency'    => ['sometimes', 'string', 'size:3'],
            'is_default'  => ['sometimes', 'boolean'],
            'is_active'   => ['sometimes', 'boolean'],
        ]);

        $updated = $this->pipelineService->update($pipeline, $request->validated());

        return ApiResponse::success(new PipelineResource($updated), 'Pipeline updated.');
    }

    public function destroy(Pipeline $pipeline): JsonResponse
    {
        $this->authorize('pipeline.delete');
        $this->pipelineService->delete($pipeline);

        return ApiResponse::success(null, 'Pipeline deleted.');
    }

    public function reorderStages(Request $request, Pipeline $pipeline): JsonResponse
    {
        $this->authorize('pipeline.update');

        $request->validate([
            'stage_ids'   => ['required', 'array'],
            'stage_ids.*' => ['integer'],
        ]);

        $this->pipelineService->reorderStages($pipeline, $request->input('stage_ids'));

        return ApiResponse::success(null, 'Stages reordered.');
    }
}
