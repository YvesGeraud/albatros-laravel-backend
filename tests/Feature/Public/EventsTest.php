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

test('featured-video prefers the live event over the most recent one with a video', function () {
    $withVideo = Event::factory()->past()->create();
    $withVideo->media()->create([
        'type' => 'youtube_video',
        'url' => 'https://www.youtube.com/watch?v=abc12345678',
        'external_id' => 'abc12345678',
    ]);
    $live = Event::factory()->live()->create();

    $response = $this->getJson('/api/v1/events/featured-video');

    $response->assertOk();
    expect($response->json('data.id'))->toBe($live->id);
});

test('featured-video falls back to the most recent event with a youtube video when nobody is live', function () {
    $older = Event::factory()->past()->create(['event_date' => now()->subMonths(2)]);
    $older->media()->create([
        'type' => 'youtube_video',
        'url' => 'https://www.youtube.com/watch?v=abc12345678',
        'external_id' => 'abc12345678',
    ]);
    $newer = Event::factory()->past()->create(['event_date' => now()->subDays(2)]);
    $newer->media()->create([
        'type' => 'youtube_live',
        'url' => 'https://www.youtube.com/watch?v=xyz98765432',
        'external_id' => 'xyz98765432',
    ]);
    Event::factory()->past()->create();

    $response = $this->getJson('/api/v1/events/featured-video');

    $response->assertOk();
    expect($response->json('data.id'))->toBe($newer->id);
});

test('featured-video returns no data when there is no live event and no video media', function () {
    Event::factory()->past()->create();

    $response = $this->getJson('/api/v1/events/featured-video');

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
