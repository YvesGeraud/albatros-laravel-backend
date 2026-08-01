<?php

use App\Models\Event;

test('events list derives past/upcoming status from event_date', function () {
    $past = Event::factory()->past()->create();
    $upcoming = Event::factory()->create();

    $response = $this->getJson('/api/v1/events');

    $response->assertOk();
    $byId = collect($response->json('data'))->keyBy('id');

    expect($byId[$past->id]['status'])->toBe('past');
    expect($byId[$upcoming->id]['status'])->toBe('upcoming');
});

test('live-now returns the live event', function () {
    $live = Event::factory()->live()->create();
    Event::factory()->create();

    $response = $this->getJson('/api/v1/events/live-now');

    $response->assertOk();
    expect($response->json('data.id'))->toBe($live->id);
    expect($response->json('data.status'))->toBe('live');
});

test('live-now returns no data when nobody is live', function () {
    Event::factory()->create();

    $response = $this->getJson('/api/v1/events/live-now');

    $response->assertOk();
    expect($response->json('data'))->toBeNull();
});

test('event detail includes media relation', function () {
    $event = Event::factory()->create();
    $event->media()->create([
        'type' => 'youtube_video',
        'url' => 'https://www.youtube.com/watch?v=abc12345678',
        'external_id' => 'abc12345678',
    ]);

    $response = $this->getJson("/api/v1/events/{$event->slug}");

    $response->assertOk();
    expect($response->json('data.media'))->toHaveCount(1);
});
