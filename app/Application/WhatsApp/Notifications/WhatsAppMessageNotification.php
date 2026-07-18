<?php

namespace App\Application\WhatsApp\Notifications;

use App\Domain\WhatsApp\Models\WhatsAppMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class WhatsAppMessageNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly WhatsAppMessage $message) {}

    /**
     * Delivery channels — broadcast for real-time, database for persistence.
     */
    public function via(object $notifiable): array
    {
        return ['broadcast', 'database'];
    }

    /**
     * Real-time broadcast payload (Pusher / Reverb).
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    /**
     * Database / array payload.
     */
    public function toArray(object $notifiable): array
    {
        $conversation = $this->message->conversation;

        return [
            'type'            => 'whatsapp_message',
            'message_id'      => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'phone_number'    => $conversation?->phone_number,
            'contact_name'    => $conversation?->contact_name,
            'content'         => $this->message->content,
            'message_type'    => $this->message->type,
            'sent_at'         => $this->message->sent_at?->toIso8601String(),
        ];
    }
}
