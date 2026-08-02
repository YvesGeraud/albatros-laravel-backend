<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TestimonialRequest;
use App\Http\Resources\TestimonialResource;
use App\Models\Testimonial;

class TestimonialController extends Controller
{
    public function index()
    {
        return TestimonialResource::collection(Testimonial::orderBy('sort_order')->get());
    }

    public function store(TestimonialRequest $request)
    {
        return new TestimonialResource(Testimonial::create($request->validated()));
    }

    public function show(Testimonial $testimonial)
    {
        return new TestimonialResource($testimonial);
    }

    public function update(TestimonialRequest $request, Testimonial $testimonial)
    {
        $testimonial->update($request->validated());

        return new TestimonialResource($testimonial);
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return response()->noContent();
    }
}
