<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuoteRequest;
use App\Http\Resources\QuoteResource;
use App\Models\Combo;
use App\Models\Product;
use App\Models\Quote;
use Illuminate\Support\Facades\DB;

class QuoteController extends Controller
{
    /**
     * Prices are re-looked-up here rather than trusting the client-sent
     * total, since the quote builder computes everything client-side.
     */
    public function store(StoreQuoteRequest $request)
    {
        $quote = DB::transaction(function () use ($request) {
            $total = 0;
            $lineItems = [];

            foreach ($request->validated('items') as $item) {
                $model = $item['quotable_type'] === 'product'
                    ? Product::findOrFail($item['quotable_id'])
                    : Combo::findOrFail($item['quotable_id']);

                $subtotal = $model->price * $item['quantity'];
                $total += $subtotal;

                $lineItems[] = [
                    'quotable_type' => $item['quotable_type'],
                    'quotable_id' => $model->id,
                    'name' => $model->name,
                    'unit_price' => $model->price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $subtotal,
                ];
            }

            $quote = Quote::create([
                'customer_name' => $request->validated('customer_name'),
                'customer_email' => $request->validated('customer_email'),
                'customer_phone' => $request->validated('customer_phone'),
                'event_date' => $request->validated('event_date'),
                'notes' => $request->validated('notes'),
                'total' => $total,
                'status' => 'pending',
            ]);

            $quote->items()->createMany($lineItems);

            return $quote;
        });

        return new QuoteResource($quote->load('items'));
    }
}
