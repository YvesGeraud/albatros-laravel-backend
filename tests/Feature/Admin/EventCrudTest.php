<?php

use App\Models\Event;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('admin can create an event', function () {
    $response = $this->postJson('/api/v1/admin/events', [
        'title' => 'Boda en Huamantla',
        'slug' => 'boda-en-huamantla-test',
        'event_date' => now()->addWeek()->toDateTimeString(),
        'latitude' => 19.3167,
        'longitude' => -97.9167,
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('events', ['slug' => 'boda-en-huamantla-test']);
});

test('admin can mark an event as live', function () {
    $event = Event::factory()->create(['is_live' => false]);

    $response = $this->putJson("/api/v1/admin/events/{$event->id}", [
        'title' => $event->title,
        'slug' => $event->slug,
        'event_date' => $event->event_date->toDateTimeString(),
        'is_live' => true,
    ]);

    $response->assertOk();
    expect($event->fresh()->is_live)->toBeTrue();
});

test('admin can delete an event', function () {
    $event = Event::factory()->create();

    $response = $this->deleteJson("/api/v1/admin/events/{$event->id}");

    $response->assertNoContent();
    $this->assertDatabaseMissing('events', ['id' => $event->id]);
});
