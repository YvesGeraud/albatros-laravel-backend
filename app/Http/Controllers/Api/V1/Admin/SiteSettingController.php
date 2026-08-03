<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteSettingController extends Controller
{
    /**
     * All editable setting keys.
     */
    private const EDITABLE_KEYS = [
        'site_name',
        'site_tagline',
        'social_facebook',
        'social_youtube',
        'social_instagram',
        'social_tiktok',
        'whatsapp_number',
        'hero_video_path',
    ];

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
                'hero_video_path'  => $heroVideoPath,
                'hero_video_url'   => $heroVideoUrl,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name'        => ['nullable', 'string', 'max:120'],
            'site_tagline'     => ['nullable', 'string', 'max:255'],
            'social_facebook'  => ['nullable', 'url', 'max:500'],
            'social_youtube'   => ['nullable', 'url', 'max:500'],
            'social_instagram' => ['nullable', 'url', 'max:500'],
            'social_tiktok'    => ['nullable', 'url', 'max:500'],
            'whatsapp_number'  => ['nullable', 'string', 'regex:/^\d{10,15}$/'],
            'hero_video_path'  => ['nullable', 'string'],
        ]);

        foreach (self::EDITABLE_KEYS as $key) {
            if (array_key_exists($key, $validated)) {
                SiteSetting::setValue($key, $validated[$key]);
            }
        }

        return $this->index();
    }
}
