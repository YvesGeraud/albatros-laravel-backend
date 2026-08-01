<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('admin can create a product', function () {
    $category = Category::factory()->create();

    $response = $this->postJson('/api/v1/admin/products', [
        'category_id' => $category->id,
        'name' => 'Bocina activa 15"',
        'slug' => 'bocina-activa-15',
        'price' => 800,
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('products', ['slug' => 'bocina-activa-15', 'category_id' => $category->id]);
});

test('creating a product requires a valid category', function () {
    $response = $this->postJson('/api/v1/admin/products', [
        'category_id' => 999999,
        'name' => 'Producto',
        'slug' => 'producto',
        'price' => 100,
    ]);

    $response->assertStatus(422);
});

test('admin can update a product price', function () {
    $product = Product::factory()->create(['price' => 100]);

    $response = $this->putJson("/api/v1/admin/products/{$product->id}", [
        'category_id' => $product->category_id,
        'name' => $product->name,
        'slug' => $product->slug,
        'price' => 250,
    ]);

    $response->assertOk();
    expect((float) $product->fresh()->price)->toBe(250.0);
});

test('admin can delete a product', function () {
    $product = Product::factory()->create();

    $response = $this->deleteJson("/api/v1/admin/products/{$product->id}");

    $response->assertNoContent();
    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});
