<?php

namespace App\Http\Controllers\Api\V1\Lead;

use App\Application\Lead\DTOs\CreateLeadDTO;
use App\Application\Lead\DTOs\LeadFilterDTO;
use App\Application\Lead\DTOs\UpdateLeadDTO;
use App\Application\Lead\Services\LeadService;
use App\Domain\Lead\Models\Lead;
use App\Http\Controllers\Controller;
use App\Http\Requests\Lead\BulkLeadRequest;
use App\Http\Requests\Lead\CreateLeadRequest;
use App\Http\Requests\Lead\UpdateLeadRequest;
use App\Http\Resources\LeadResource;
use App\Support\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function __construct(private readonly LeadService $leadService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('leads.view');

        $paginated = $this->leadService->list(
            LeadFilterDTO::fromArray($request->query())
        );

        return ApiResponse::paginated($paginated->through(
            fn ($lead) => new LeadResource($lead)
        ));
    }

    public function store(CreateLeadRequest $request): JsonResponse
    {
        $lead = $this->leadService->create(
            CreateLeadDTO::fromArray($request->validated())
        );

        return ApiResponse::success(new LeadResource($lead), 'Lead created.', 201);
    }

    public function show(Lead $lead): JsonResponse
    {
        $this->authorize('leads.view');

        return ApiResponse::success(
            new LeadResource($lead->load(['assignedTo', 'activities.user']))
        );
    }

    public function update(UpdateLeadRequest $request, Lead $lead): JsonResponse
    {
        $updated = $this->leadService->update(
            $lead,
            UpdateLeadDTO::fromArray($request->validated())
        );

        return ApiResponse::success(new LeadResource($updated), 'Lead updated.');
    }

    public function destroy(Lead $lead): JsonResponse
    {
        $this->authorize('leads.delete');
        $this->leadService->delete($lead);

        return ApiResponse::success(null, 'Lead deleted.');
    }

    public function stats(): JsonResponse
    {
        $this->authorize('leads.view');

        return ApiResponse::success($this->leadService->stats());
    }

    public function bulkAssign(BulkLeadRequest $request): JsonResponse
    {
        $count = $this->leadService->bulkAssign(
            $request->validated('ids'),
            $request->validated('assigned_to')
        );

        return ApiResponse::success(['updated' => $count], "{$count} leads assigned.");
    }

    public function bulkStatus(BulkLeadRequest $request): JsonResponse
    {
        $count = $this->leadService->bulkUpdateStatus(
            $request->validated('ids'),
            $request->validated('status')
        );

        return ApiResponse::success(['updated' => $count], "{$count} leads updated.");
    }
}
