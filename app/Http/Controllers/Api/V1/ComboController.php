<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ComboResource;
use App\Models\Combo;

class ComboController extends Controller
{
    public function index()
    {
        return ComboResource::collection(
            Combo::with('products')->where('is_active', true)->orderBy('sort_order')->get()
        );
    }

    public function show(Combo $combo)
    {
        return new ComboResource($combo->load('products'));
    }
}
