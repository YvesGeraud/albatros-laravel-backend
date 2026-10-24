<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'package' => ['required', 'in:basico,estandar,golden'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required_without:customer_email', 'nullable', 'string', 'max:30'],
            'customer_email' => ['required_without:customer_phone', 'nullable', 'email', 'max:255'],
            'event_date' => ['nullable', 'date', 'after_or_equal:today'],
            'guests' => ['required', 'integer', 'min:1', 'max:5000'],
            'event_location' => ['required', 'string', 'max:255'],
            'venue_type' => ['required', 'in:salon,jardin'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
