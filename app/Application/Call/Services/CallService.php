<?php

namespace App\Application\Call\Services;

use App\Application\Call\DTOs\CallFilterDTO;
use App\Application\Call\DTOs\InitiateCallDTO;
use App\Application\Call\DTOs\UpdateCallDTO;
use App\Domain\Call\Contracts\CallRepositoryInterface;
use App\Domain\Call\Models\CallLog;
use App\Infrastructure\Call\Services\ExotelService;
use App\Infrastructure\Call\Services\RetellService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class CallService
{
    public function __construct(
        private readonly CallRepositoryInterface $callRepository,
        private readonly AiCallAnalysisService   $aiService,
        private readonly ExotelService           $exotelService,
        private readonly RetellService           $retellService,
    ) {}

    public function list(CallFilterDTO $dto): LengthAwarePaginator
    {
        return $this->callRepository->paginate([
            'search'     => $dto->search,
            'status'     => $dto->status,
            'direction'  => $dto->direction,
            'user_id'    => $dto->userId,
            'contact_id' => $dto->contactId,
            'lead_id'    => $dto->leadId,
            'date_from'  => $dto->dateFrom,
            'date_to'    => $dto->dateTo,
        ], $dto->perPage);
    }

    private function defaultProvider(): string
    {
        $settingsPath = storage_path('app/call_settings.json');
        if (file_exists($settingsPath)) {
            $data = json_decode(file_get_contents($settingsPath), true);
            if (is_array($data) && isset($data['default_provider'])) {
                return $data['default_provider'];
            }
        }
        return config('services.calls.default_provider', 'retell');
    }

    public function initiate(InitiateCallDTO $dto): CallLog
    {
        $provider = $dto->provider ?? $this->defaultProvider();

        // Create the call record first
        $call = $this->callRepository->create([
            'tenant_id'    => Auth::user()->tenant_id,
            'user_id'      => Auth::id(),
            'contact_id'   => $dto->contactId,
            'lead_id'      => $dto->leadId,
            'phone_number' => $dto->phoneNumber,
            'direction'    => $dto->direction,
            'notes'        => $dto->notes,
            'status'       => 'initiated',
            'started_at'   => now(),
        ]);

        if ($provider === 'retell') {
            $metadata = array_filter([
                'lead_id'      => $dto->leadId,
                'contact_id'   => $dto->contactId,
                'crm_call_id'  => $call->id,
                'call_purpose' => $dto->callPurpose,
            ]);
            $providerId = $this->retellService->initiateCall(
                $dto->phoneNumber,
                $metadata,
                $dto->agentId  // pass selected agent, falls back to default if null
            );
        } else {
            $providerId = $this->exotelService->initiateCall($dto->phoneNumber);
        }

        if ($providerId) {
            $call = $this->callRepository->update($call, [
                'provider_call_id' => $providerId,
                'status'           => 'ringing',
                'meta'             => array_filter([
                    'retell_agent_id' => $dto->agentId,
                    'call_purpose'    => $dto->callPurpose,
                ]),
            ]);
        }

        return $call;
    }

    public function update(CallLog $call, UpdateCallDTO $dto): CallLog
    {
        $data = array_filter([
            'status'          => $dto->status,
            'duration'        => $dto->duration,
            'recording_url'   => $dto->recordingUrl,
            'transcript'      => $dto->transcript,
            'provider_call_id'=> $dto->providerCallId,
            'started_at'      => $dto->startedAt,
            'ended_at'        => $dto->endedAt,
        ], fn ($v) => $v !== null);

        return $this->callRepository->update($call, $data);
    }

    /**
     * Generate AI summary for a call using its transcript.
     */
    public function generateSummary(CallLog $call): CallLog
    {
        if (!$call->transcript) {
            throw new \RuntimeException('No transcript available for AI analysis.');
        }

        $context = [
            'contact_name' => $call->contact?->full_name,
            'agent_name'   => $call->user?->name,
        ];

        $result = $this->aiService->analyzeTranscript($call->transcript, $context);

        return $this->callRepository->update($call, [
            'ai_summary'  => $result['summary'],
            'ai_insights' => $result['insights'],
        ]);
    }

    public function stats(): array
    {
        return $this->callRepository->getStatsByTenant(Auth::user()->tenant_id);
    }

    public function listRetellAgents(): array
    {
        $agents = $this->retellService->listAgents();

        // Return only the fields the UI needs
        return array_map(fn($a) => [
            'agent_id'   => $a['agent_id'],
            'agent_name' => $a['agent_name'] ?? 'Unnamed Agent',
        ], $agents);
    }

    /**
     * Initiate calls to multiple phone numbers in sequence.
     * Returns summary of queued/failed calls.
     */
    public function bulkCall(array $phones, ?string $notes = null, ?string $agentId = null, ?string $callPurpose = null): array
    {
        $queued  = 0;
        $failed  = 0;
        $callIds = [];

        foreach ($phones as $phone) {
            try {
                $dto = InitiateCallDTO::fromArray([
                    'phone_number' => $phone,
                    'direction'    => 'outbound',
                    'notes'        => $notes,
                    'agent_id'     => $agentId,
                    'call_purpose' => $callPurpose,
                ]);
                $call      = $this->initiate($dto);
                $callIds[] = $call->id;
                $queued++;

                usleep(300000); // 300ms delay between calls
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Bulk call failed for {$phone}: " . $e->getMessage());
                $failed++;
            }
        }

        return [
            'queued'   => $queued,
            'failed'   => $failed,
            'total'    => count($phones),
            'call_ids' => $callIds,
        ];
    }

    public function report(array $filters): array
    {
        $data = $this->callRepository->getReportData(Auth::user()->tenant_id, $filters);

        // Attach AI narrative if there's enough data
        if (($data['summary']['total'] ?? 0) > 0) {
            $data['ai_narrative'] = $this->aiService->generateReportNarrative($data);
        } else {
            $data['ai_narrative'] = 'No call data available for the selected period.';
        }

        return $data;
    }
}
