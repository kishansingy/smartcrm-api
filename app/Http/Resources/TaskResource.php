<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'description'  => $this->description,
            'type'         => $this->type,
            'priority'     => $this->priority,
            'status'       => $this->status,
            'is_overdue'   => $this->isOverdue(),
            'due_date'     => $this->due_date?->toDateString(),
            'due_time'     => $this->due_time,
            'reminder_at'  => $this->reminder_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'meta'         => $this->meta,
            'assigned_to'  => new UserResource($this->whenLoaded('assignedTo')),
            'created_by'   => new UserResource($this->whenLoaded('createdBy')),
            'lead'         => new LeadResource($this->whenLoaded('lead')),
            'contact'      => new ContactResource($this->whenLoaded('contact')),
            'deal'         => new DealResource($this->whenLoaded('deal')),
            'comments'     => TaskCommentResource::collection($this->whenLoaded('comments')),
            'created_at'   => $this->created_at?->toISOString(),
            'updated_at'   => $this->updated_at?->toISOString(),
        ];
    }
}
