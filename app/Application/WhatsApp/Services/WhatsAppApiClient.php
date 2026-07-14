<?php

namespace App\Application\WhatsApp\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppApiClient
{
    private string $apiUrl;
    private string $token;
    private string $phoneId;

    public function __construct()
    {
        $this->apiUrl  = rtrim(config('services.whatsapp.api_url', 'https://graph.facebook.com/v19.0'), '/');
        $this->token   = config('services.whatsapp.token', '');
        $this->phoneId = config('services.whatsapp.phone_id', '');
    }

    public function sendText(string $to, string $text): array
    {
        return $this->send([
            'messaging_product' => 'whatsapp',
            'to'                => $this->normalizePhone($to),
            'type'              => 'text',
            'text'              => ['body' => $text],
        ]);
    }

    public function sendTemplate(string $to, string $name, string $language, array $components = []): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to'                => $this->normalizePhone($to),
            'type'              => 'template',
            'template'          => [
                'name'     => $name,
                'language' => ['code' => $language],
            ],
        ];

        if (! empty($components)) {
            $payload['template']['components'] = $components;
        }

        return $this->send($payload);
    }

    public function sendMedia(string $to, string $type, string $mediaUrl, ?string $caption = null): array
    {
        $media = ['link' => $mediaUrl];
        if ($caption) {
            $media['caption'] = $caption;
        }

        return $this->send([
            'messaging_product' => 'whatsapp',
            'to'                => $this->normalizePhone($to),
            'type'              => $type,
            $type               => $media,
        ]);
    }

    public function markRead(string $messageId): array
    {
        return $this->send([
            'messaging_product' => 'whatsapp',
            'status'            => 'read',
            'message_id'        => $messageId,
        ]);
    }

    public function getTemplates(): array
    {
        $response = Http::withToken($this->token)
            ->get("{$this->apiUrl}/{$this->phoneId}/message_templates");

        if ($response->failed()) {
            Log::error('WhatsApp get templates failed', ['response' => $response->json()]);
            return [];
        }

        return $response->json('data', []);
    }

    private function send(array $payload): array
    {
        if (empty($this->token) || empty($this->phoneId)) {
            Log::warning('WhatsApp API not configured.');
            return ['error' => 'WhatsApp not configured', 'messages' => [['id' => 'mock_' . uniqid()]]];
        }

        $response = Http::withToken($this->token)
            ->post("{$this->apiUrl}/{$this->phoneId}/messages", $payload);

        if ($response->failed()) {
            Log::error('WhatsApp send failed', [
                'payload'  => $payload,
                'response' => $response->json(),
            ]);
            throw new \RuntimeException('WhatsApp API error: ' . ($response->json('error.message') ?? 'Unknown error'));
        }

        return $response->json();
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }
}
