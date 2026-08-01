<?php

use App\Models\Event;
use App\Models\EventMedia;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('admin can add a youtube video to an event', function () {
    $event = Event::factory()->create();

    $response = $this->postJson("/api/v1/admin/events/{$event->id}/media", [
        'type' => 'youtube_video',
        'url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
        'external_id' => 'aqz-KE-bpKQ',
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('event_media', ['event_id' => $event->id, 'type' => 'youtube_video']);
});

test('admin can upload and attach a photo to an event', function () {
    Storage::fake(config('filesystems.default'));
    $event = Event::factory()->create();

    $upload = $this->postJson('/api/v1/admin/uploads', [
        'file' => UploadedFile::fake()->image('foto.jpg'),
        'folder' => 'events',
    ])->assertCreated();

    $response = $this->postJson("/api/v1/admin/events/{$event->id}/media", [
        'type' => 'photo',
        'url' => $upload->json('path'),
        'caption' => 'Foto del evento',
    ]);

    $response->assertCreated();
    expect($response->json('data.url'))->toContain($upload->json('path'));
});

/**
 * Regression test: the shallow nested route for events.media singularizes
 * "media" to "medium" (Laravel's Str::singular quirk), so the controller's
 * update()/destroy() parameters must be named $medium, not $media, or
 * implicit route-model binding silently injects an empty unsaved model and
 * the request "succeeds" (204) without actually changing anything in the DB.
 */
test('deleting an event media item actually removes it from the database', function () {
    $event = Event::factory()->create();
    $media = $event->media()->create([
        'type' => 'youtube_video',
        'url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
        'external_id' => 'aqz-KE-bpKQ',
    ]);

    $response = $this->deleteJson("/api/v1/admin/media/{$media->id}");

    $response->assertNoContent();
    expect(EventMedia::find($media->id))->toBeNull();
});

test('updating an event media item actually changes it in the database', function () {
    $event = Event::factory()->create();
    $media = $event->media()->create([
        'type' => 'youtube_video',
        'url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
        'external_id' => 'aqz-KE-bpKQ',
    ]);

    $response = $this->putJson("/api/v1/admin/media/{$media->id}", [
        'type' => 'youtube_video',
        'url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
        'external_id' => 'aqz-KE-bpKQ',
        'caption' => 'Actualizado',
    ]);

    $response->assertOk();
    expect($media->fresh()->caption)->toBe('Actualizado');
});
