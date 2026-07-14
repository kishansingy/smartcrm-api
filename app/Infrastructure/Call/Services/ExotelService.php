<?php

namespace App\Infrastructure\Call\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExotelService
{
    private string $sid;
    private string $apiKey;
    private string $apiToken;
    private string $from;
    private string $appId;
    private string $baseUrl;

    public function __construct()
    {
        $this->sid       = config('services.exotel.sid', '');
        $this->apiKey    = config('services.exotel.api_key', '');
        $this->apiToken  = config('services.exotel.api_token', '');
        $this->from      = config('services.exotel.from', '');
        $this->appId     = config('services.exotel.app_id', '');
        $subdomain       = config('services.exotel.subdomain', 'api.exotel.com');

        // Exotel API v2 endpoint
        $this->baseUrl = "https://{$this->apiKey}:{$this->apiToken}@{$subdomain}/v1/Accounts/{$this->sid}";
    }

    /**
     * Initiate an outbound call via Exotel.
     * Returns the Exotel Call SID on success, null on failure.
     */
    public function initiateCall(string $toNumber, ?string $statusCallbackUrl = null): ?string
    {
        // Use config webhook URL if none passed explicitly
        $callbackUrl = $statusCallbackUrl ?? config('services.exotel.webhook_url');

        $payload = [
            'From'     => $this->from,
            'To'       => $toNumber,
            'CallerId' => $this->from,
            'Url'      => "http://my.exotel.com/{$this->sid}/exoml/start_voice/{$this->appId}",
        ];

        if ($callbackUrl) {
            $payload['StatusCallback']      = $callbackUrl;
            $payload['StatusCallbackEvent'] = 'terminal'; // fires when call ends
        }

        try {
            $response = Http::asForm()
                ->timeout(15)
                ->post("{$this->baseUrl}/Calls/connect.json", $payload);

            if ($response->failed()) {
                Log::error('Exotel call initiation failed', [
                    'status'   => $response->status(),
                    'response' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();

            return $data['Call']['Sid'] ?? null;

        } catch (\Throwable $e) {
            Log::error('Exotel service exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Fetch call details from Exotel by Call SID.
     */
    public function getCallDetails(string $callSid): ?array
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/Calls/{$callSid}.json");

            if ($response->failed()) {
                return null;
            }

            return $response->json('Call');

        } catch (\Throwable $e) {
            Log::error('Exotel getCallDetails exception', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
