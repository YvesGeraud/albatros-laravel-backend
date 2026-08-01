<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:facebook_post,youtube_video,youtube_live,photo'],
            'url' => [
                'required',
                Rule::when(
                    $this->input('type') === 'photo',
                    ['string', 'max:500'],
                    ['url', 'max:500'],
                ),
            ],
            'external_id' => ['nullable', 'string', 'max:50'],
            'caption' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }
}
