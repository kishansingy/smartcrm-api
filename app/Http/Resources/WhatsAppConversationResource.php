<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WhatsAppConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'phone_number'    => $this->phone_number,
            'contact_name'    => $this->contact_name,
            'status'          => $this->status,
            'last_message'    => $this->last_message,
            'last_message_at' => $this->last_message_at?->toISOString(),
            'unread_count'    => $this->unread_count,
            'assigned_to'     => new UserResource($this->whenLoaded('assignedTo')),
            'contact'         => new ContactResource($this->whenLoaded('contact')),
            'created_at'      => $this->created_at?->toISOString(),
        ];
    }
}
