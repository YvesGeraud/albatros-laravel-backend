<?php

use App\Http\Controllers\Api\V1\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\ComboController as AdminComboController;
use App\Http\Controllers\Api\V1\Admin\EventController as AdminEventController;
use App\Http\Controllers\Api\V1\Admin\EventMediaController as AdminEventMediaController;
use App\Http\Controllers\Api\V1\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\V1\Admin\QuoteController as AdminQuoteController;
use App\Http\Controllers\Api\V1\Admin\TestimonialController as AdminTestimonialController;
use App\Http\Controllers\Api\V1\Admin\UploadController as AdminUploadController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ComboController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\QuoteController;
use App\Http\Controllers\Api\V1\TestimonialController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category:slug}', [CategoryController::class, 'show']);

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{product:slug}', [ProductController::class, 'show']);

    Route::get('combos', [ComboController::class, 'index']);
    Route::get('combos/{combo:slug}', [ComboController::class, 'show']);

    Route::get('catalog', CatalogController::class);

    Route::get('events', [EventController::class, 'index']);
    Route::get('events/live-now', [EventController::class, 'liveNow']);
    Route::get('events/featured-video', [EventController::class, 'featuredVideo']);
    Route::get('events/{event:slug}', [EventController::class, 'show']);

    Route::get('testimonials', [TestimonialController::class, 'index']);

    Route::post('quotes', [QuoteController::class, 'store']);

    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    Route::get('user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');

    Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
        Route::apiResource('categories', AdminCategoryController::class);
        Route::apiResource('products', AdminProductController::class);
        Route::apiResource('combos', AdminComboController::class);
        Route::apiResource('events', AdminEventController::class);
        Route::apiResource('events.media', AdminEventMediaController::class)
            ->shallow()
            ->only(['index', 'store', 'update', 'destroy']);
        Route::get('quotes', [AdminQuoteController::class, 'index']);
        Route::get('quotes/{quote}', [AdminQuoteController::class, 'show']);
        Route::patch('quotes/{quote}', [AdminQuoteController::class, 'update']);
        Route::post('uploads', [AdminUploadController::class, 'store']);
        Route::apiResource('testimonials', AdminTestimonialController::class);
    });
});
