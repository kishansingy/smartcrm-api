<?php

namespace App\Domain\WhatsApp\Events;

use App\Domain\WhatsApp\Models\WhatsAppMessage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhatsAppMessageSent
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly WhatsAppMessage $message) {}
}
