<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'full_name'   => $this->full_name,
            'first_name'  => $this->first_name,
            'last_name'   => $this->last_name,
            'email'       => $this->email,
            'phone'       => $this->phone,
            'mobile'      => $this->mobile,
            'company'     => $this->company,
            'job_title'   => $this->job_title,
            'department'  => $this->department,
            'website'     => $this->website,
            'address'     => $this->address,
            'type'        => $this->type,
            'status'      => $this->status,
            'source'      => $this->source,
            'tags'        => $this->tags ?? [],
            'note'        => $this->getRawOriginal('notes'),
            'meta'        => $this->meta,
            'assigned_to'   => new UserResource($this->whenLoaded('assignedTo')),
            'contact_notes' => ContactNoteResource::collection($this->whenLoaded('contactNotes')),
            'created_at'  => $this->created_at?->toISOString(),
            'updated_at'  => $this->updated_at?->toISOString(),
        ];
    }
}
