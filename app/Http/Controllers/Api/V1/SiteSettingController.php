<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;

class SiteSettingController extends Controller
{
    /**
     * Return all public-facing site settings.
     */
    public function index()
    {
        $settings = SiteSetting::all()->pluck('value', 'key')->all();

        $heroVideoPath = $settings['hero_video_path'] ?? null;
        $heroVideoUrl = SiteSetting::formatUrl($heroVideoPath);

        return response()->json([
            'data' => [
                'site_name'        => $settings['site_name'] ?? 'Albatros Tlaxcala',
                'site_tagline'     => $settings['site_tagline'] ?? 'Sonido, iluminación, pista de baile y bailarines para eventos inolvidables.',
                'social_facebook'  => $settings['social_facebook'] ?? '',
                'social_youtube'   => $settings['social_youtube'] ?? '',
                'social_instagram' => $settings['social_instagram'] ?? '',
                'social_tiktok'    => $settings['social_tiktok'] ?? '',
                'whatsapp_number'  => $settings['whatsapp_number'] ?? '',
                'hero_video_url'   => $heroVideoUrl,
            ],
        ]);
    }

    /**
     * Legacy endpoint — kept for backward compatibility.
     */
    public function hero()
    {
        $heroVideoPath = SiteSetting::getValue('hero_video_path');
        $heroVideoUrl = SiteSetting::formatUrl($heroVideoPath);

        return response()->json([
            'data' => [
                'hero_video_url' => $heroVideoUrl,
            ],
        ]);
    }
}
