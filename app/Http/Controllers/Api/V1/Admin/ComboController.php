<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ComboRequest;
use App\Http\Resources\ComboResource;
use App\Models\Combo;

class ComboController extends Controller
{
    public function index()
    {
        return ComboResource::collection(Combo::with('products')->orderBy('sort_order')->get());
    }

    public function store(ComboRequest $request)
    {
        $combo = Combo::create($request->safe()->except('products'));
        $this->syncProducts($combo, $request->validated('products', []));

        return new ComboResource($combo->load('products'));
    }

    public function show(Combo $combo)
    {
        return new ComboResource($combo->load('products'));
    }

    public function update(ComboRequest $request, Combo $combo)
    {
        $combo->update($request->safe()->except('products'));

        if ($request->has('products')) {
            $this->syncProducts($combo, $request->validated('products', []));
        }

        return new ComboResource($combo->load('products'));
    }

    public function destroy(Combo $combo)
    {
        $combo->delete();

        return response()->noContent();
    }

    private function syncProducts(Combo $combo, array $products): void
    {
        $combo->products()->sync(
            collect($products)->mapWithKeys(fn ($p) => [$p['id'] => ['quantity' => $p['quantity']]])
        );
    }
}
