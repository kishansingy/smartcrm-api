<?php

namespace App\Http\Controllers\Api\V1\Call;

use App\Application\Call\DTOs\CallFilterDTO;
use App\Application\Call\DTOs\InitiateCallDTO;
use App\Application\Call\DTOs\UpdateCallDTO;
use App\Application\Call\Services\CallService;
use App\Domain\Call\Models\CallLog;
use App\Http\Controllers\Controller;
use App\Http\Requests\Call\InitiateCallRequest;
use App\Http\Requests\Call\UpdateCallRequest;
use App\Http\Resources\CallLogResource;
use App\Support\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CallController extends Controller
{
    public function __construct(private readonly CallService $callService) {}

    /**
     * List call history with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('calls.view');

        $paginated = $this->callService->list(
            CallFilterDTO::fromArray($request->query())
        );

        return ApiResponse::paginated($paginated->through(
            fn ($call) => new CallLogResource($call)
        ));
    }

    /**
     * Show a single call log (includes transcript if requested).
     */
    public function show(CallLog $callLog): JsonResponse
    {
        $this->authorize('calls.view');

        $callLog->load(['user', 'contact', 'lead']);

        return ApiResponse::success(new CallLogResource($callLog));
    }

    /**
     * Initiate / register a new call.
     */
    public function store(InitiateCallRequest $request): JsonResponse
    {
        try {
            $call = $this->callService->initiate(
                InitiateCallDTO::fromArray($request->validated())
            );
            return ApiResponse::success(new CallLogResource($call), 'Call initiated.', 201);
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }
    }

    /**
     * Update call status, duration, transcript, etc. (e.g. from a provider webhook).
     */
    public function update(UpdateCallRequest $request, CallLog $callLog): JsonResponse
    {
        $updated = $this->callService->update(
            $callLog,
            UpdateCallDTO::fromArray($request->validated())
        );

        return ApiResponse::success(new CallLogResource($updated), 'Call updated.');
    }

    /**
     * Trigger AI summary generation for a completed call with a transcript.
     */
    public function generateSummary(CallLog $callLog): JsonResponse
    {
        $this->authorize('calls.view');

        try {
            $updated = $this->callService->generateSummary($callLog);
            return ApiResponse::success(new CallLogResource($updated), 'AI summary generated.');
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }
    }

    /**
     * List available Retell agents for the agent selector dropdown.
     */
    public function retellAgents(): JsonResponse
    {
        $this->authorize('calls.make');

        $agents = $this->callService->listRetellAgents();

        return ApiResponse::success($agents);
    }

    /**
     * Bulk call — initiate calls to multiple phone numbers.
     */
    public function bulkCall(Request $request): JsonResponse
    {
        $this->authorize('calls.make');

        $request->validate([
            'phones'       => ['required', 'array', 'min:1', 'max:100'],
            'phones.*'     => ['required', 'string', 'max:30'],
            'notes'        => ['nullable', 'string', 'max:1000'],
            'agent_id'     => ['nullable', 'string'],
            'call_purpose' => ['nullable', 'string', 'max:100'],
        ]);

        $result = $this->callService->bulkCall(
            $request->input('phones'),
            $request->input('notes'),
            $request->input('agent_id'),
            $request->input('call_purpose'),
        );

        return ApiResponse::success($result, "Bulk call initiated: {$result['queued']} queued, {$result['failed']} failed.");
    }

    /**
     * Call stats for dashboard.
     */
    public function stats(): JsonResponse
    {
        $this->authorize('calls.view');

        return ApiResponse::success($this->callService->stats());
    }

    /**
     * Generate a full call report with AI narrative.
     */
    public function report(Request $request): JsonResponse
    {
        $this->authorize('reports.view');

        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date'],
            'user_id'   => ['nullable', 'integer'],
        ]);

        $data = $this->callService->report($request->only(['date_from', 'date_to', 'user_id']));

        // Return full report: summary, by_status, by_agent, daily
        unset($data['calls']);

        return ApiResponse::success($data, 'Call report generated.');
    }

    /**
     * Get call history for a specific contact.
     */
    public function contactHistory(Request $request, int $contactId): JsonResponse
    {
        $this->authorize('calls.view');

        $paginated = $this->callService->list(
            CallFilterDTO::fromArray(array_merge($request->query(), ['contact_id' => $contactId]))
        );

        return ApiResponse::paginated($paginated->through(
            fn ($call) => new CallLogResource($call)
        ));
    }
}
