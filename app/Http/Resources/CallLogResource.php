<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CallLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'phone_number'     => $this->phone_number,
            'direction'        => $this->direction,
            'status'           => $this->status,
            'duration'         => $this->duration,
            'duration_formatted' => $this->duration_formatted,
            'provider_call_id' => $this->provider_call_id,
            'recording_url'    => $this->recording_url,
            'notes'            => $this->notes,
            'has_transcript'   => !empty($this->transcript),
            'transcript'       => $this->when($request->has('with_transcript'), $this->transcript),
            'ai_summary'       => $this->ai_summary,
            'ai_insights'      => $this->ai_insights,
            'started_at'       => $this->started_at?->toIso8601String(),
            'ended_at'         => $this->ended_at?->toIso8601String(),
            'created_at'       => $this->created_at?->toIso8601String(),
            'agent'            => $this->whenLoaded('user', fn () => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ]),
            'contact'          => $this->whenLoaded('contact', fn () => $this->contact ? [
                'id'   => $this->contact->id,
                'name' => $this->contact->full_name,
                'phone'=> $this->contact->phone,
            ] : null),
            'lead'             => $this->whenLoaded('lead', fn () => $this->lead ? [
                'id'   => $this->lead->id,
                'name' => $this->lead->full_name,
            ] : null),
        ];
    }
}
