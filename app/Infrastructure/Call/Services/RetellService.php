<?php

namespace App\Infrastructure\Call\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RetellService
{
    private string $apiKey;
    private string $agentId;
    private string $fromNumber;
    private string $baseUrl = 'https://api.retellai.com/v2';

    public function __construct()
    {
        $this->apiKey      = config('services.retell.api_key', '');
        $this->agentId     = config('services.retell.agent_id', '');
        $this->fromNumber  = config('services.retell.from_number', '');
    }

    /**
     * Fetch all agents from Retell workspace.
     */
    public function listAgents(): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(10)
                ->get("{$this->baseUrl}/list-agents");

            if ($response->failed()) {
                return [];
            }

            return $response->json() ?? [];

        } catch (\Throwable $e) {
            Log::error('Retell listAgents exception', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Initiate an outbound phone call via Retell.
     * Returns the Retell call_id on success, null on failure.
     */
    public function initiateCall(string $toNumber, array $metadata = [], ?string $agentId = null): ?string
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('Retell API key is not configured. Set RETELL_API_KEY in .env');
        }

        $payload = [
            'from_number' => $this->fromNumber,
            'to_number'   => $toNumber,
            'agent_id'    => $agentId ?? $this->agentId,
            // Retell requires all dynamic variables to be strings
            'retell_llm_dynamic_variables' => array_map(
                fn($v) => (string) $v,
                array_filter($metadata, fn($v) => $v !== null)
            ),
        ];

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(15)
                ->post("{$this->baseUrl}/create-phone-call", $payload);

            if ($response->failed()) {
                Log::error('Retell call initiation failed', [
                    'status'   => $response->status(),
                    'response' => $response->body(),
                ]);
                throw new \RuntimeException('Retell API error: ' . $response->status() . ' — ' . $response->body());
            }

            return $response->json('call_id');

        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Retell service exception', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Retell service unavailable: ' . $e->getMessage());
        }
    }

    /**
     * Fetch call details from Retell by call_id.
     */
    public function getCallDetails(string $callId): ?array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(10)
                ->get("{$this->baseUrl}/get-call/{$callId}");

            if ($response->failed()) {
                return null;
            }

            return $response->json();

        } catch (\Throwable $e) {
            Log::error('Retell getCallDetails exception', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
