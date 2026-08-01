<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuoteResource;
use App\Models\Quote;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function index()
    {
        return QuoteResource::collection(Quote::orderByDesc('created_at')->get());
    }

    public function show(Quote $quote)
    {
        return new QuoteResource($quote->load('items'));
    }

    public function update(Request $request, Quote $quote)
    {
        $request->validate([
            'status' => ['required', 'in:pending,contacted,closed'],
        ]);

        $quote->update(['status' => $request->status]);

        return new QuoteResource($quote->load('items'));
    }
}
