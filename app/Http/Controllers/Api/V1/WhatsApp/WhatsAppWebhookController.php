<?php

namespace App\Http\Controllers\Api\V1\WhatsApp;

use App\Application\WhatsApp\Jobs\ProcessWhatsAppWebhook;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WhatsAppWebhookController extends Controller
{
    /**
     * Meta webhook verification (GET).
     */
    public function verify(Request $request): Response
    {
        $mode      = $request->query('hub_mode');
        $token     = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $verifyToken = config('services.whatsapp.verify_token', 'smartcrm_verify');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            return response($challenge, 200);
        }

        return response('Verification failed', 403);
    }

    /**
     * Incoming webhook events (POST) — dispatched to queue.
     */
    public function receive(Request $request): Response
    {
        $payload = $request->all();

        // Validate it's from WhatsApp
        if (($payload['object'] ?? '') !== 'whatsapp_business_account') {
            return response('', 200);
        }

        ProcessWhatsAppWebhook::dispatch($payload);

        // Always return 200 immediately — Meta will retry if we don't
        return response('', 200);
    }
}
