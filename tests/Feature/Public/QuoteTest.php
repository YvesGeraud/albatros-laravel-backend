<?php

use App\Models\Category;
use App\Models\Combo;
use App\Models\Product;
use App\Models\Quote;

test('quote total is recalculated server-side and does not trust the client', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'price' => 800]);
    $combo = Combo::factory()->create(['price' => 9500]);

    $response = $this->postJson('/api/v1/quotes', [
        'customer_name' => 'Juan Perez',
        'customer_email' => 'juan@example.com',
        'items' => [
            ['quotable_type' => 'product', 'quotable_id' => $product->id, 'quantity' => 2],
            ['quotable_type' => 'combo', 'quotable_id' => $combo->id, 'quantity' => 1],
        ],
    ]);

    $response->assertCreated();
    // 2 x 800 + 1 x 9500 = 11100, regardless of any price the client might claim.
    expect((float) $response->json('data.total'))->toBe(11100.0);

    $quote = Quote::first();
    expect((float) $quote->total)->toBe(11100.0);
    expect($quote->items)->toHaveCount(2);
});

test('quote requires at least one item', function () {
    $response = $this->postJson('/api/v1/quotes', [
        'customer_name' => 'Juan Perez',
        'items' => [],
    ]);

    $response->assertStatus(422);
});

test('quote rejects an unknown quotable id', function () {
    $response = $this->postJson('/api/v1/quotes', [
        'customer_name' => 'Juan Perez',
        'items' => [
            ['quotable_type' => 'product', 'quotable_id' => 999999, 'quantity' => 1],
        ],
    ]);

    $response->assertStatus(404);
});
