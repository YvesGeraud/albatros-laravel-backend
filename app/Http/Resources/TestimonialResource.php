<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TestimonialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_name' => $this->customer_name,
            'event_type' => $this->event_type,
            'content' => $this->content,
            'rating' => $this->rating,
            'avatar_path' => $this->avatar_path,
            'avatar_url' => $this->avatar_path
                ? Storage::disk(config('filesystems.default'))->url($this->avatar_path)
                : null,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];
    }
}
