<?php

test('health check confirms the database is reachable', function () {
    $response = $this->getJson('/health');

    $response->assertOk();
    $response->assertJson([
        'estado' => 'ok',
        'base_datos' => 'conectada',
    ]);
});
