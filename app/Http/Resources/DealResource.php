<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DealResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'title'               => $this->title,
            'value'               => $this->value,
            'currency'            => $this->currency,
            'probability'         => $this->probability,
            'status'              => $this->status,
            'lost_reason'         => $this->lost_reason,
            'notes'               => $this->notes,
            'meta'                => $this->meta,
            'expected_close_date' => $this->expected_close_date?->toDateString(),
            'closed_at'           => $this->closed_at?->toISOString(),
            'pipeline'            => new PipelineResource($this->whenLoaded('pipeline')),
            'stage'               => new PipelineStageResource($this->whenLoaded('stage')),
            'contact'             => new ContactResource($this->whenLoaded('contact')),
            'lead'                => new LeadResource($this->whenLoaded('lead')),
            'assigned_to'         => new UserResource($this->whenLoaded('assignedTo')),
            'activities'          => DealActivityResource::collection($this->whenLoaded('activities')),
            'created_at'          => $this->created_at?->toISOString(),
            'updated_at'          => $this->updated_at?->toISOString(),
        ];
    }
}
