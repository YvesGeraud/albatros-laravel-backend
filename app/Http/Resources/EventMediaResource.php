<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class EventMediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_id' => $this->event_id,
            'type' => $this->type,
            'url' => $this->type === 'photo'
                ? Storage::disk(config('filesystems.default'))->url($this->url)
                : $this->url,
            'external_id' => $this->external_id,
            'caption' => $this->caption,
            'sort_order' => $this->sort_order,
        ];
    }
}
