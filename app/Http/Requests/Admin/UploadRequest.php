<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'image', 'max:5120', 'mimes:jpg,jpeg,png,webp,gif'],
            'folder' => ['required', Rule::in(['products', 'combos', 'events', 'testimonials'])],
        ];
    }
}
