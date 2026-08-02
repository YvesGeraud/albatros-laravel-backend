<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteSettingController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::all()->pluck('value', 'key')->all();

        $heroVideoPath = $settings['hero_video_path'] ?? null;
        $heroVideoUrl = $heroVideoPath
            ? Storage::disk(config('filesystems.default'))->url($heroVideoPath)
            : null;

        return response()->json([
            'data' => [
                'hero_video_path' => $heroVideoPath,
                'hero_video_url' => $heroVideoUrl,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'hero_video_path' => ['nullable', 'string'],
        ]);

        if (array_key_exists('hero_video_path', $validated)) {
            SiteSetting::setValue('hero_video_path', $validated['hero_video_path']);
        }

        return $this->index();
    }
}
