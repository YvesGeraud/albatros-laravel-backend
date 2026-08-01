<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;

class EventController extends Controller
{
    public function index()
    {
        return EventResource::collection(Event::with('media')->orderByDesc('event_date')->get());
    }

    public function store(EventRequest $request)
    {
        return new EventResource(Event::create($request->validated()));
    }

    public function show(Event $event)
    {
        return new EventResource($event->load('media'));
    }

    public function update(EventRequest $request, Event $event)
    {
        $event->update($request->validated());

        return new EventResource($event->load('media'));
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return response()->noContent();
    }
}
