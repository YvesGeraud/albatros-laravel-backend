<?php

use App\Models\Category;
use App\Models\Combo;
use App\Models\Product;

test('catalog endpoint only returns active products and combos', function () {
    $category = Category::factory()->create();
    $activeProduct = Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);
    Product::factory()->create(['category_id' => $category->id, 'is_active' => false]);

    $activeCombo = Combo::factory()->create(['is_active' => true]);
    Combo::factory()->create(['is_active' => false]);

    $response = $this->getJson('/api/v1/catalog');

    $response->assertOk();
    $productIds = collect($response->json('products'))->pluck('id');
    $comboIds = collect($response->json('combos'))->pluck('id');

    expect($productIds)->toContain($activeProduct->id);
    expect($comboIds)->toContain($activeCombo->id);
    expect($response->json('categories'))->not->toBeEmpty();
});

test('products endpoint returns only active products', function () {
    $category = Category::factory()->create();
    Product::factory()->create(['category_id' => $category->id, 'is_active' => false]);

    $response = $this->getJson('/api/v1/products');

    $response->assertOk();
    expect($response->json('data'))->toBeEmpty();
});
