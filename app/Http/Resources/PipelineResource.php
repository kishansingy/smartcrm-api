<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PipelineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'currency'    => $this->currency,
            'is_default'  => $this->is_default,
            'is_active'   => $this->is_active,
            'stages'      => PipelineStageResource::collection($this->whenLoaded('stages')),
            'deals_count' => $this->whenCounted('deals'),
            'created_at'  => $this->created_at?->toISOString(),
        ];
    }
}
