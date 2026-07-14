<?php

namespace App\Domain\WhatsApp\Events;

use App\Domain\WhatsApp\Models\WhatsAppMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhatsAppMessageReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly WhatsAppMessage $message) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("tenant.{$this->message->conversation->tenant_id}.whatsapp"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.received';
    }
}
