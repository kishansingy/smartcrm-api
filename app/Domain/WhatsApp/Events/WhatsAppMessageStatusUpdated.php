<?php

namespace App\Domain\WhatsApp\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhatsAppMessageStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int    $tenantId,
        public readonly string $waMessageId,
        public readonly string $status,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("tenant.{$this->tenantId}.whatsapp"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.status';
    }

    public function broadcastWith(): array
    {
        return [
            'wa_message_id' => $this->waMessageId,
            'status'        => $this->status,
        ];
    }
}
