<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WhatsAppMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'wa_message_id' => $this->wa_message_id,
            'direction'     => $this->direction,
            'type'          => $this->type,
            'content'       => $this->content,
            'media_url'     => $this->media_url,
            'template_name' => $this->template_name,
            'status'        => $this->status,
            'error_message' => $this->error_message,
            'sent_at'       => $this->sent_at?->toISOString(),
            'delivered_at'  => $this->delivered_at?->toISOString(),
            'read_at'       => $this->read_at?->toISOString(),
            'user'          => new UserResource($this->whenLoaded('user')),
            'conversation'  => $this->whenLoaded('conversation', fn () => [
                'id'           => $this->conversation->id,
                'phone_number' => $this->conversation->phone_number,
                'contact_name' => $this->conversation->contact_name,
            ]),
            'created_at'    => $this->created_at?->toISOString(),
        ];
    }
}
