<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;

class SiteSettingController extends Controller
{
    public function hero()
    {
        $heroVideoPath = SiteSetting::getValue('hero_video_path');
        $heroVideoUrl = $heroVideoPath
            ? Storage::disk(config('filesystems.default'))->url($heroVideoPath)
            : null;

        return response()->json([
            'data' => [
                'hero_video_url' => $heroVideoUrl,
            ],
        ]);
    }
}
