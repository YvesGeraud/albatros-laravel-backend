<?php

use App\Http\Controllers\Api\V1\Admin\SiteSettingController as AdminSiteSettingController;
use App\Http\Controllers\Api\V1\Admin\UploadController as AdminUploadController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ComboController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\QuoteController;
use App\Http\Controllers\Api\V1\SiteSettingController;
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
    Route::get('settings', [SiteSettingController::class, 'index']);
    Route::get('settings/hero', [SiteSettingController::class, 'hero']);

    // Endpoints públicos que escriben en la BD: limitados para evitar spam.
    Route::post('quotes', [QuoteController::class, 'store'])->middleware('throttle:10,1');
    Route::post('quotes/package', [QuoteController::class, 'storePackage'])->middleware('throttle:10,1');

    // 5 intentos por minuto por IP para frenar ataques de fuerza bruta.
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    Route::get('user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');

    Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
        Route::post('uploads', [AdminUploadController::class, 'store']);
        Route::get('settings', [AdminSiteSettingController::class, 'index']);
        Route::put('settings', [AdminSiteSettingController::class, 'update']);
    });
});
