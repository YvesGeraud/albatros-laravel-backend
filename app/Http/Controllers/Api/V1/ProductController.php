<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        return ProductResource::collection(
            Product::where('is_active', true)->orderBy('sort_order')->get()
        );
    }

    public function show(Product $product)
    {
        return new ProductResource($product);
    }
}
