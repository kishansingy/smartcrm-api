<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
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
            'company'     => $this->company,
            'job_title'   => $this->job_title,
            'source'      => $this->source,
            'status'      => $this->status,
            'priority'    => $this->priority,
            'score'       => $this->score,
            'notes'       => $this->notes,
            'meta'        => $this->meta,
            'assigned_to' => new UserResource($this->whenLoaded('assignedTo')),
            'activities'  => LeadActivityResource::collection($this->whenLoaded('activities')),
            'created_at'  => $this->created_at?->toISOString(),
            'updated_at'  => $this->updated_at?->toISOString(),
        ];
    }
}
