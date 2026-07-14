<?php

namespace App\Http\Controllers\Api\V1\Call;

use App\Application\Call\Services\AiCallAnalysisService;
use App\Domain\Call\Contracts\CallRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class RetellWebhookController extends Controller
{
    public function __construct(
        private readonly CallRepositoryInterface $callRepository,
        private readonly AiCallAnalysisService   $aiService,
    ) {}

    /**
     * Retell posts call events here.
     * Events: call_started, call_ended, call_analyzed
     */
    public function handle(Request $request): Response
    {
        $event = $request->input('event');
        $data  = $request->input('call', []);

        Log::info('Retell webhook received', ['event' => $event, 'call_id' => $data['call_id'] ?? null]);

        match ($event) {
            'call_ended'    => $this->handleCallEnded($data),
            'call_analyzed' => $this->handleCallAnalyzed($data),
            default         => null,
        };

        return response('OK', 200);
    }

    private function handleCallEnded(array $data): void
    {
        $callId = $data['call_id'] ?? null;
        if (!$callId) return;

        $call = $this->callRepository->findByProviderCallId($callId);
        if (!$call) {
            Log::warning('Retell webhook: call not found', ['call_id' => $callId]);
            return;
        }

        $updateData = [
            'status'   => $this->mapDisconnectReason($data['disconnection_reason'] ?? ''),
            'ended_at' => now(),
        ];

        // Duration in ms → convert to seconds
        if (!empty($data['end_timestamp']) && !empty($data['start_timestamp'])) {
            $updateData['duration'] = (int) round(($data['end_timestamp'] - $data['start_timestamp']) / 1000);
        }

        if (!empty($data['recording_url'])) {
            $updateData['recording_url'] = $data['recording_url'];
        }

        if (!empty($data['transcript'])) {
            $updateData['transcript'] = $data['transcript'];
        }

        $this->callRepository->update($call, $updateData);
    }

    private function handleCallAnalyzed(array $data): void
    {
        $callId = $data['call_id'] ?? null;
        if (!$callId) return;

        $call = $this->callRepository->findByProviderCallId($callId);
        if (!$call) return;

        $updateData = [];

        // Retell sentiment: Positive, Neutral, Negative
        if (!empty($data['call_analysis']['user_sentiment'])) {
            $updateData['ai_insights'] = array_merge($call->ai_insights ?? [], [
                'sentiment' => $data['call_analysis']['user_sentiment'],
            ]);
        }

        if (!empty($data['call_analysis']['call_summary'])) {
            $updateData['ai_summary'] = $data['call_analysis']['call_summary'];
        }

        if ($updateData) {
            $this->callRepository->update($call, $updateData);
        }
    }

    private function mapDisconnectReason(string $reason): string
    {
        return match (strtolower($reason)) {
            'user_hangup', 'agent_hangup' => 'completed',
            'call_transfer'               => 'completed',
            'voicemail_reached'           => 'no_answer',
            'inactivity', 'machine_detected' => 'no_answer',
            'error', 'dial_failed'        => 'failed',
            default                       => 'completed',
        };
    }
}
