<?php

namespace App\Application\WhatsApp\Jobs;

use App\Application\WhatsApp\DTOs\SendMessageDTO;
use App\Application\WhatsApp\Services\WhatsAppService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppBroadcast implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;
    public int $backoff = 10;

    public function __construct(
        private readonly array  $recipient,   // ['phone' => '', 'name' => '', 'body_params' => []]
        private readonly string $templateName,
        private readonly string $language,
        private readonly array  $components,  // header + shared body params already built
        private readonly int    $tenantId,
        private readonly int    $userId,
    ) {}

    public function handle(WhatsAppService $service): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $components = $this->components;

        // Override body params if per-recipient params were provided
        if (!empty($this->recipient['body_params'])) {
            // Remove existing body component if present, replace with per-recipient values
            $components = array_filter($components, fn($c) => strtolower($c['type'] ?? '') !== 'body');
            $components = array_values($components);
            $components[] = [
                'type'       => 'body',
                'parameters' => array_map(fn($v) => ['type' => 'text', 'text' => $v], $this->recipient['body_params']),
            ];
        }

        $dto = SendMessageDTO::fromArray([
            'to'                  => $this->recipient['phone'],
            'type'                => 'template',
            'template_name'       => $this->templateName,
            'template_language'   => $this->language,
            'template_components' => $components,
        ]);

        // Temporarily set auth context so WhatsAppService can find the tenant/user
        auth()->onceUsingId($this->userId);

        $service->sendMessage($dto);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('WhatsApp broadcast job failed', [
            'phone'    => $this->recipient['phone'],
            'template' => $this->templateName,
            'error'    => $e->getMessage(),
        ]);
    }
}
