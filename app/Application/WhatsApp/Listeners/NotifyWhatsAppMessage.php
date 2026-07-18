<?php

namespace App\Application\WhatsApp\Listeners;

use App\Domain\WhatsApp\Events\WhatsAppMessageReceived;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class NotifyWhatsAppMessage implements ShouldQueue
{
    public int $tries   = 3;
    public int $timeout = 30;

    /**
     * Handle incoming WhatsApp message — notify tenant users who should be alerted.
     */
    public function handle(WhatsAppMessageReceived $event): void
    {
        $message      = $event->message;
        $conversation = $message->conversation;

        if (! $conversation) {
            Log::warning('WhatsAppMessageReceived: conversation not loaded on message #' . $message->id);
            return;
        }

        // Notify the assigned agent if the conversation has one
        $assignedUserId = $conversation->assigned_to;

        if ($assignedUserId) {
            $user = User::find($assignedUserId);
            $user?->notify(new \App\Application\WhatsApp\Notifications\WhatsAppMessageNotification($message));
        }
    }
}
