<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PipelineStageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'color'       => $this->color,
            'position'    => $this->position,
            'probability' => $this->probability,
            'is_won'      => $this->is_won,
            'is_lost'     => $this->is_lost,
            'deals_count' => $this->whenCounted('deals'),
        ];
    }
}
