<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
    Storage::fake(config('filesystems.default'));
});

test('admin can upload a valid image', function () {
    $response = $this->postJson('/api/v1/admin/uploads', [
        'file' => UploadedFile::fake()->image('product.jpg'),
        'folder' => 'products',
    ]);

    $response->assertCreated();
    expect($response->json('path'))->toStartWith('products/');
    expect($response->json('url'))->not->toBeEmpty();

    Storage::disk(config('filesystems.default'))->assertExists($response->json('path'));
});

test('admin can upload a testimonial avatar', function () {
    $response = $this->postJson('/api/v1/admin/uploads', [
        'file' => UploadedFile::fake()->image('avatar.jpg'),
        'folder' => 'testimonials',
    ]);

    $response->assertCreated();
    expect($response->json('path'))->toStartWith('testimonials/');
});

test('upload rejects a non-image file', function () {
    $response = $this->postJson('/api/v1/admin/uploads', [
        'file' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        'folder' => 'products',
    ]);

    $response->assertStatus(422);
});

test('upload rejects an oversized file', function () {
    $response = $this->postJson('/api/v1/admin/uploads', [
        'file' => UploadedFile::fake()->image('big.jpg')->size(6000),
        'folder' => 'products',
    ]);

    $response->assertStatus(422);
});

test('upload rejects an invalid folder', function () {
    $response = $this->postJson('/api/v1/admin/uploads', [
        'file' => UploadedFile::fake()->image('product.jpg'),
        'folder' => 'not-a-real-folder',
    ]);

    $response->assertStatus(422);
});
