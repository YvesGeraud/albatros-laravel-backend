<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventMediaRequest;
use App\Http\Resources\EventMediaResource;
use App\Models\Event;
use App\Models\EventMedia;

class EventMediaController extends Controller
{
    public function index(Event $event)
    {
        return EventMediaResource::collection($event->media()->orderBy('sort_order')->get());
    }

    public function store(EventMediaRequest $request, Event $event)
    {
        return new EventMediaResource($event->media()->create($request->validated()));
    }

    /**
     * Laravel singularizes the "media" resource segment to "medium" for the
     * shallow route parameter, so this must be $medium (not $media) or
     * implicit route-model binding silently fails and injects an empty,
     * unsaved model instead of throwing.
     */
    public function update(EventMediaRequest $request, EventMedia $medium)
    {
        $medium->update($request->validated());

        return new EventMediaResource($medium);
    }

    public function destroy(EventMedia $medium)
    {
        $medium->delete();

        return response()->noContent();
    }
}
