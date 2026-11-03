<?php

test('admin endpoints reject requests without a session', function (string $method, string $uri) {
    $response = $this->json($method, $uri);

    $response->assertStatus(401);
})->with([
    ['GET', '/api/v1/admin/settings'],
    ['PUT', '/api/v1/admin/settings'],
    ['POST', '/api/v1/admin/uploads'],
]);
