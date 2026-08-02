<?php

use App\Models\Testimonial;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('admin can list all testimonials, including inactive ones', function () {
    Testimonial::factory()->create(['is_active' => false]);
    Testimonial::factory()->create(['is_active' => true]);

    $response = $this->getJson('/api/v1/admin/testimonials');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(2);
});

test('admin can create a testimonial', function () {
    $response = $this->postJson('/api/v1/admin/testimonials', [
        'customer_name' => 'Mariana y Luis',
        'event_type' => 'Boda',
        'content' => 'Excelente servicio.',
        'rating' => 5,
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('testimonials', ['customer_name' => 'Mariana y Luis']);
});

test('creating a testimonial requires customer_name and content', function () {
    $response = $this->postJson('/api/v1/admin/testimonials', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['customer_name', 'content']);
});

test('admin can update a testimonial', function () {
    $testimonial = Testimonial::factory()->create();

    $response = $this->putJson("/api/v1/admin/testimonials/{$testimonial->id}", [
        'customer_name' => $testimonial->customer_name,
        'content' => 'Contenido actualizado.',
        'is_active' => false,
    ]);

    $response->assertOk();
    expect($testimonial->fresh()->content)->toBe('Contenido actualizado.');
    expect($testimonial->fresh()->is_active)->toBeFalse();
});

test('admin can delete a testimonial', function () {
    $testimonial = Testimonial::factory()->create();

    $response = $this->deleteJson("/api/v1/admin/testimonials/{$testimonial->id}");

    $response->assertNoContent();
    $this->assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
});
