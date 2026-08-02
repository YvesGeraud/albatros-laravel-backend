<?php

test('admin endpoints reject requests without a session', function (string $method, string $uri) {
    $response = $this->json($method, $uri);

    $response->assertStatus(401);
})->with([
    ['GET', '/api/v1/admin/categories'],
    ['POST', '/api/v1/admin/categories'],
    ['GET', '/api/v1/admin/products'],
    ['POST', '/api/v1/admin/products'],
    ['GET', '/api/v1/admin/combos'],
    ['POST', '/api/v1/admin/combos'],
    ['GET', '/api/v1/admin/events'],
    ['POST', '/api/v1/admin/events'],
    ['GET', '/api/v1/admin/events/1/media'],
    ['POST', '/api/v1/admin/events/1/media'],
    ['GET', '/api/v1/admin/quotes'],
    ['POST', '/api/v1/admin/uploads'],
    ['GET', '/api/v1/admin/testimonials'],
    ['POST', '/api/v1/admin/testimonials'],
]);
