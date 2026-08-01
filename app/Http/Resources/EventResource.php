<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'venue_name' => $this->venue_name,
            'address' => $this->address,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'event_date' => $this->event_date?->toIso8601String(),
            'is_live' => $this->is_live,
            'status' => $this->is_live ? 'live' : ($this->event_date?->isPast() ? 'past' : 'upcoming'),
            'media' => EventMediaResource::collection($this->whenLoaded('media')),
        ];
    }
}
