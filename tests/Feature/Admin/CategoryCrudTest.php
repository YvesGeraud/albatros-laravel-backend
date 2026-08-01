<?php

use App\Models\Category;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('admin can create a category', function () {
    $response = $this->postJson('/api/v1/admin/categories', [
        'name' => 'Sonido',
        'slug' => 'sonido',
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('categories', ['slug' => 'sonido']);
});

test('creating a category requires a unique slug', function () {
    Category::factory()->create(['slug' => 'sonido']);

    $response = $this->postJson('/api/v1/admin/categories', [
        'name' => 'Otro sonido',
        'slug' => 'sonido',
    ]);

    $response->assertStatus(422);
});

test('admin can update a category', function () {
    $category = Category::factory()->create();

    $response = $this->putJson("/api/v1/admin/categories/{$category->id}", [
        'name' => 'Nuevo nombre',
        'slug' => $category->slug,
    ]);

    $response->assertOk();
    expect($category->fresh()->name)->toBe('Nuevo nombre');
});

test('admin can delete a category', function () {
    $category = Category::factory()->create();

    $response = $this->deleteJson("/api/v1/admin/categories/{$category->id}");

    $response->assertNoContent();
    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});
