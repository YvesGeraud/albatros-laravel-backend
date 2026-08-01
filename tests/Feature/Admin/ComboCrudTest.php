<?php

use App\Models\Combo;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('admin can create a combo with products', function () {
    $productA = Product::factory()->create();
    $productB = Product::factory()->create();

    $response = $this->postJson('/api/v1/admin/combos', [
        'name' => 'Combo Fiesta',
        'slug' => 'combo-fiesta',
        'price' => 5000,
        'products' => [
            ['id' => $productA->id, 'quantity' => 2],
            ['id' => $productB->id, 'quantity' => 1],
        ],
    ]);

    $response->assertCreated();
    $combo = Combo::where('slug', 'combo-fiesta')->first();
    expect($combo->products)->toHaveCount(2);
    expect($combo->products->firstWhere('id', $productA->id)->pivot->quantity)->toBe(2);
});

test('admin can resync a combo products list on update', function () {
    $combo = Combo::factory()->create();
    $productA = Product::factory()->create();
    $productB = Product::factory()->create();
    $combo->products()->attach($productA->id, ['quantity' => 1]);

    $response = $this->putJson("/api/v1/admin/combos/{$combo->id}", [
        'name' => $combo->name,
        'slug' => $combo->slug,
        'price' => $combo->price,
        'products' => [
            ['id' => $productB->id, 'quantity' => 3],
        ],
    ]);

    $response->assertOk();
    $combo->refresh();
    expect($combo->products)->toHaveCount(1);
    expect($combo->products->first()->id)->toBe($productB->id);
});

test('admin can delete a combo', function () {
    $combo = Combo::factory()->create();

    $response = $this->deleteJson("/api/v1/admin/combos/{$combo->id}");

    $response->assertNoContent();
    $this->assertDatabaseMissing('combos', ['id' => $combo->id]);
});
