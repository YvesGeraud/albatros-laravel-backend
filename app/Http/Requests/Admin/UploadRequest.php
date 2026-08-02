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
        $folder = $this->input('folder');

        if ($folder === 'hero') {
            return [
                'file' => ['required', 'file', 'max:102400', 'mimes:mp4,webm,mov,avi,mkv,jpg,jpeg,png,webp'],
                'folder' => ['required', Rule::in(['products', 'combos', 'events', 'testimonials', 'hero'])],
            ];
        }

        return [
            'file' => ['required', 'file', 'image', 'max:5120', 'mimes:jpg,jpeg,png,webp,gif'],
            'folder' => ['required', Rule::in(['products', 'combos', 'events', 'testimonials', 'hero'])],
        ];
    }
}
