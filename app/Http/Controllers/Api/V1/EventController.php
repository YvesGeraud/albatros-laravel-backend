<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::with('media')->orderByDesc('event_date');

        if ($request->boolean('is_live')) {
            $query->where('is_live', true);
        }

        if ($request->filled('status')) {
            match ($request->string('status')->toString()) {
                'past' => $query->where('is_live', false)->where('event_date', '<', now()),
                'upcoming' => $query->where('is_live', false)->where('event_date', '>=', now()),
                default => null,
            };
        }

        return EventResource::collection($query->get());
    }

    public function show(Event $event)
    {
        return new EventResource($event->load('media'));
    }

    public function liveNow()
    {
        $event = Event::with('media')->where('is_live', true)->first();

        return $event ? new EventResource($event) : response()->json(null);
    }

    /**
     * Video for the homepage's big hero banner: the live event's video if
     * there's a live event right now, otherwise the most recent event that
     * has a YouTube video attached (a "highlight reel" fallback).
     */
    public function featuredVideo()
    {
        $event = Event::with('media')->where('is_live', true)->first()
            ?? Event::with('media')
                ->whereHas('media', fn ($q) => $q->whereIn('type', ['youtube_video', 'youtube_live']))
                ->orderByDesc('event_date')
                ->first();

        return $event ? new EventResource($event) : response()->json(null);
    }
}
