<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'package' => $this->package,
            'event_date' => $this->event_date?->toDateString(),
            'guests' => $this->guests,
            'event_location' => $this->event_location,
            'venue_type' => $this->venue_type,
            'notes' => $this->notes,
            'total' => (float) $this->total,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(fn ($item) => [
                    'id' => $item->id,
                    'quotable_type' => $item->quotable_type,
                    'quotable_id' => $item->quotable_id,
                    'name' => $item->name,
                    'unit_price' => (float) $item->unit_price,
                    'quantity' => $item->quantity,
                    'subtotal' => (float) $item->subtotal,
                ]);
            }),
        ];
    }
}
