<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TestimonialResource;
use App\Models\Testimonial;

class TestimonialController extends Controller
{
    public function index()
    {
        return TestimonialResource::collection(
            Testimonial::where('is_active', true)->orderBy('sort_order')->get()
        );
    }
}
