<?php

namespace App\Application\WhatsApp\Jobs;

use App\Application\WhatsApp\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWhatsAppWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 30;

    public function __construct(private readonly array $payload) {}

    public function handle(WhatsAppService $service): void
    {
        $service->handleWebhook($this->payload);
    }
}
