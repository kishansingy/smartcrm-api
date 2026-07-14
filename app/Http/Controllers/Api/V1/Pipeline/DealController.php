<?php

namespace App\Http\Controllers\Api\V1\Pipeline;

use App\Application\Pipeline\DTOs\CreateDealDTO;
use App\Application\Pipeline\DTOs\DealFilterDTO;
use App\Application\Pipeline\DTOs\UpdateDealDTO;
use App\Application\Pipeline\Services\DealService;
use App\Domain\Pipeline\Models\Deal;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pipeline\CreateDealRequest;
use App\Http\Requests\Pipeline\UpdateDealRequest;
use App\Http\Resources\DealResource;
use App\Support\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function __construct(private readonly DealService $dealService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('pipeline.view');

        $paginated = $this->dealService->list(
            DealFilterDTO::fromArray($request->query())
        );

        return ApiResponse::paginated($paginated->through(
            fn ($deal) => new DealResource($deal)
        ));
    }

    public function store(CreateDealRequest $request): JsonResponse
    {
        $deal = $this->dealService->create(
            CreateDealDTO::fromArray($request->validated())
        );

        return ApiResponse::success(new DealResource($deal), 'Deal created.', 201);
    }

    public function show(Deal $deal): JsonResponse
    {
        $this->authorize('pipeline.view');

        return ApiResponse::success(
            new DealResource($deal->load(['stage', 'pipeline', 'contact', 'lead', 'assignedTo', 'activities.user']))
        );
    }

    public function update(UpdateDealRequest $request, Deal $deal): JsonResponse
    {
        $updated = $this->dealService->update(
            $deal,
            UpdateDealDTO::fromArray($request->validated())
        );

        return ApiResponse::success(new DealResource($updated), 'Deal updated.');
    }

    public function destroy(Deal $deal): JsonResponse
    {
        $this->authorize('pipeline.delete');
        $this->dealService->delete($deal);

        return ApiResponse::success(null, 'Deal deleted.');
    }

    public function move(Request $request, Deal $deal): JsonResponse
    {
        $this->authorize('pipeline.update');

        $request->validate([
            'stage_id' => ['required', 'integer', 'exists:pipeline_stages,id'],
        ]);

        $updated = $this->dealService->moveToStage($deal, $request->integer('stage_id'));

        return ApiResponse::success(new DealResource($updated), 'Deal moved.');
    }

    public function stats(): JsonResponse
    {
        $this->authorize('pipeline.view');

        return ApiResponse::success($this->dealService->stats());
    }

    public function kanban(Request $request): JsonResponse
    {
        $this->authorize('pipeline.view');

        // Auto-resolve to default pipeline if no pipeline_id provided
        $tenantId   = $request->user()->tenant_id;
        $pipelineId = $request->integer('pipeline_id') ?: \App\Domain\Pipeline\Models\Pipeline::where('tenant_id', $tenantId)
            ->where('is_default', true)
            ->value('id');

        if (!$pipelineId) {
            $pipelineId = \App\Domain\Pipeline\Models\Pipeline::where('tenant_id', $tenantId)->value('id');
        }

        if (!$pipelineId) {
            return ApiResponse::success([], 'No pipeline found.');
        }

        return ApiResponse::success(
            $this->dealService->kanban($pipelineId)
        );
    }
}
