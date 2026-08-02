<?php

use App\Models\Testimonial;

test('testimonials list only returns active testimonials, ordered by sort_order', function () {
    $second = Testimonial::factory()->create(['sort_order' => 2]);
    $first = Testimonial::factory()->create(['sort_order' => 1]);
    Testimonial::factory()->create(['is_active' => false]);

    $response = $this->getJson('/api/v1/testimonials');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(2);
    expect($response->json('data.0.id'))->toBe($first->id);
    expect($response->json('data.1.id'))->toBe($second->id);
});
