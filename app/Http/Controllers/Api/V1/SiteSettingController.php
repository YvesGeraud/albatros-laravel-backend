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

        // Helper to decode JSON settings safely
        $decodeJson = function ($key) use ($settings) {
            if (empty($settings[$key])) {
                return null;
            }
            $decoded = json_decode($settings[$key], true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        };

        // Check if an event is currently live in DB
        $liveEvent = \App\Models\Event::with('media')->where('is_live', true)->first();
        $liveActive = isset($settings['live_stream_active'])
            ? filter_var($settings['live_stream_active'], FILTER_VALIDATE_BOOLEAN)
            : ($liveEvent !== null);

        $liveYoutubeMedia = $liveEvent?->media?->firstWhere('type', 'youtube_live')
            ?? $liveEvent?->media?->firstWhere('type', 'youtube_video');

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
                'hero_kicker'      => $settings['hero_kicker'] ?? 'TLAXCALA · SONIDO Y EVENTOS',
                'hero_subtitle'    => $settings['hero_subtitle'] ?? 'Sonido, iluminación, pista de baile y bailarines para que tu evento sea inolvidable.',
                'hero_phrases'     => $settings['hero_phrases'] ?? '¡Haz tu Fiesta Única!|Sonido · Iluminación · Pista de Baile|Albatros Tlaxcala',
                'about_title'      => $settings['about_title'] ?? 'Sobre Grupo Albatros',
                'about_description'=> $settings['about_description'] ?? '',
                'about_bullets'    => $settings['about_bullets'] ?? '',
                'about_image_url'  => SiteSetting::formatUrl($settings['about_image_path'] ?? null),

                // CMS Section datasets
                'brand_narrative'          => $settings['brand_narrative'] ?? '',
                'brand_bullets_data'       => $decodeJson('brand_bullets_data'),
                'brand_stats_data'         => $decodeJson('brand_stats_data'),
                'trayectoria_eras_data'    => $decodeJson('trayectoria_eras_data'),
                'curated_photos_data'      => $decodeJson('curated_photos_data'),
                'curated_videos_data'      => $decodeJson('curated_videos_data'),
                'services_list_data'       => $decodeJson('services_list_data'),
                'simulator_packages_data'  => $decodeJson('simulator_packages_data'),
                'testimonials_list_data'   => $decodeJson('testimonials_list_data'),

                // Live Stream CMS fields
                'live_stream_active'       => $liveActive,
                'live_stream_title'        => $settings['live_stream_title'] ?? ($liveEvent?->title ?? 'Transmisión En Vivo — Grupo Albatros'),
                'live_stream_youtube_id'   => $settings['live_stream_youtube_id'] ?? ($liveYoutubeMedia?->external_id ?? 'kJQP7kiw5Fk'),
                'live_stream_venue'        => $settings['live_stream_venue'] ?? ($liveEvent?->venue_name ?? 'Hacienda Soltepec, Huamantla, Tlaxcala'),
                'live_stream_address'      => $settings['live_stream_address'] ?? ($liveEvent?->address ?? 'Carretera Huamantla-Puebla Km 3, Huamantla, Tlaxcala'),
                'live_stream_lat'          => $settings['live_stream_lat'] ?? ($liveEvent?->latitude ?? 19.3182),
                'live_stream_lng'          => $settings['live_stream_lng'] ?? ($liveEvent?->longitude ?? -98.2375),
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
