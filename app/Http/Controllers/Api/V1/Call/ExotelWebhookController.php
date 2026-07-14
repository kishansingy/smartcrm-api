<?php

namespace App\Http\Controllers\Api\V1\Call;

use App\Domain\Call\Contracts\CallRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ExotelWebhookController extends Controller
{
    public function __construct(private readonly CallRepositoryInterface $callRepository) {}

    /**
     * Exotel posts call status here when a call ends.
     * Exotel sends form fields: CallSid, Status, RecordingUrl, Duration, etc.
     */
    public function handle(Request $request): Response
    {
        Log::info('Exotel webhook received', $request->all());

        $callSid  = $request->input('CallSid');
        $status   = $request->input('Status');       // completed, busy, no-answer, failed
        $duration = $request->input('RecordingDuration') ?? $request->input('Duration');
        $recording = $request->input('RecordingUrl');

        if (!$callSid) {
            return response('Missing CallSid', 400);
        }

        // Find our call log by provider_call_id
        $call = $this->callRepository->findByProviderCallId($callSid);

        if (!$call) {
            Log::warning('Exotel webhook: call not found', ['sid' => $callSid]);
            return response('OK', 200); // always 200 to stop Exotel retries
        }

        $updateData = [
            'status'   => $this->mapStatus($status),
            'ended_at' => now(),
        ];

        if ($duration) {
            $updateData['duration'] = (int) $duration;
        }

        if ($recording) {
            $updateData['recording_url'] = $recording;
        }

        $this->callRepository->update($call, $updateData);

        return response('OK', 200);
    }

    /**
     * Map Exotel status values to our internal statuses.
     */
    private function mapStatus(string $exotelStatus): string
    {
        return match (strtolower($exotelStatus)) {
            'completed'  => 'completed',
            'busy'       => 'busy',
            'no-answer'  => 'no_answer',
            'failed'     => 'failed',
            'canceled'   => 'cancelled',
            default      => 'completed',
        };
    }
}
