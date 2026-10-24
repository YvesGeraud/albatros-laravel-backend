<?php

use App\Models\User;

test('admin can save event packages and the public settings return them', function () {
    $this->actingAs(User::factory()->create());

    $packages = [
        [
            'id' => 'golden',
            'title' => 'Paquete Golden Plus',
            'highlights' => ['Line Array', 'Chisperos'],
            'image' => 'https://example.com/golden.jpg',
        ],
    ];

    $this->putJson('/api/v1/admin/settings', ['event_packages_data' => $packages])->assertOk();

    $response = $this->getJson('/api/v1/settings');

    $response->assertOk();
    expect($response->json('data.event_packages_data'))->toBe($packages);
});
