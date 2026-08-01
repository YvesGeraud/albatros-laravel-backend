<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ComboResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Combo;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * Combined categories+products+combos payload so the quote builder
     * can load the full catalog in a single request and price everything
     * client-side without further round-trips.
     */
    public function __invoke(Request $request)
    {
        return response()->json([
            'categories' => CategoryResource::collection(
                Category::orderBy('sort_order')->get()
            ),
            'products' => ProductResource::collection(
                Product::where('is_active', true)->orderBy('sort_order')->get()
            ),
            'combos' => ComboResource::collection(
                Combo::with('products')->where('is_active', true)->orderBy('sort_order')->get()
            ),
        ]);
    }
}
