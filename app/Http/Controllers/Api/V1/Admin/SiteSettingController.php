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
        'hero_kicker',
        'hero_subtitle',
        'hero_phrases',
        'about_title',
        'about_description',
        'about_bullets',
        'about_image_path',
        // Section CMS datasets
        'brand_narrative',
        'brand_bullets_data',
        'brand_stats_data',
        'trayectoria_eras_data',
        'curated_photos_data',
        'curated_videos_data',
        'services_list_data',
        'simulator_packages_data',
        'testimonials_list_data',
        // Live Stream CMS fields
        'live_stream_active',
        'live_stream_title',
        'live_stream_youtube_id',
        'live_stream_venue',
        'live_stream_address',
        'live_stream_lat',
        'live_stream_lng',
    ];

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
                'hero_video_path'  => $heroVideoPath,
                'hero_video_url'   => $heroVideoUrl,
                'hero_kicker'      => $settings['hero_kicker'] ?? 'TLAXCALA · SONIDO Y EVENTOS',
                'hero_subtitle'    => $settings['hero_subtitle'] ?? 'Sonido, iluminación, pista de baile y bailarines para que tu evento sea inolvidable.',
                'hero_phrases'     => $settings['hero_phrases'] ?? '¡Haz tu Fiesta Única!|Sonido · Iluminación · Pista de Baile|Albatros Tlaxcala',
                'about_title'      => $settings['about_title'] ?? 'Sobre Grupo Albatros',
                'about_description'=> $settings['about_description'] ?? '',
                'about_bullets'    => $settings['about_bullets'] ?? '',
                'about_image_path' => $settings['about_image_path'] ?? null,
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

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name'        => ['nullable', 'string', 'max:120'],
            'site_tagline'     => ['nullable', 'string', 'max:255'],
            'social_facebook'  => ['nullable', 'string', 'max:500'],
            'social_youtube'   => ['nullable', 'string', 'max:500'],
            'social_instagram' => ['nullable', 'string', 'max:500'],
            'social_tiktok'    => ['nullable', 'string', 'max:500'],
            'whatsapp_number'  => ['nullable', 'string', 'regex:/^\d{10,15}$/'],
            'hero_video_path'  => ['nullable', 'string'],
            'hero_kicker'      => ['nullable', 'string', 'max:120'],
            'hero_subtitle'    => ['nullable', 'string', 'max:500'],
            'hero_phrases'     => ['nullable', 'string', 'max:1000'],
            'about_title'      => ['nullable', 'string', 'max:200'],
            'about_description'=> ['nullable', 'string', 'max:2000'],
            'about_bullets'    => ['nullable', 'string', 'max:2000'],
            'about_image_path' => ['nullable', 'string'],

            // CMS Section JSON and texts
            'brand_narrative'          => ['nullable', 'string', 'max:3000'],
            'brand_bullets_data'       => ['nullable'],
            'brand_stats_data'         => ['nullable'],
            'trayectoria_eras_data'    => ['nullable'],
            'curated_photos_data'      => ['nullable'],
            'curated_videos_data'      => ['nullable'],
            'services_list_data'       => ['nullable'],
            'simulator_packages_data'  => ['nullable'],
            'testimonials_list_data'   => ['nullable'],

            // Live Stream
            'live_stream_active'       => ['nullable'],
            'live_stream_title'        => ['nullable', 'string', 'max:255'],
            'live_stream_youtube_id'   => ['nullable', 'string', 'max:100'],
            'live_stream_venue'        => ['nullable', 'string', 'max:255'],
            'live_stream_address'      => ['nullable', 'string', 'max:255'],
            'live_stream_lat'          => ['nullable'],
            'live_stream_lng'          => ['nullable'],
        ]);

        foreach (self::EDITABLE_KEYS as $key) {
            if (array_key_exists($key, $validated)) {
                $val = $validated[$key];
                if (is_array($val) || is_object($val)) {
                    $val = json_encode($val, JSON_UNESCAPED_UNICODE);
                } elseif (is_bool($val)) {
                    $val = $val ? '1' : '0';
                }
                SiteSetting::setValue($key, $val);
            }
        }

        // Synchronize Live Stream with Event model
        if (array_key_exists('live_stream_active', $validated)) {
            $isActive = filter_var($validated['live_stream_active'], FILTER_VALIDATE_BOOLEAN);

            if (! $isActive) {
                \App\Models\Event::where('is_live', true)->update(['is_live' => false]);
            } else {
                \App\Models\Event::where('is_live', true)->update(['is_live' => false]);
                $title = $validated['live_stream_title'] ?? SiteSetting::getValue('live_stream_title', 'Transmisión En Vivo — Grupo Albatros');
                $venue = $validated['live_stream_venue'] ?? SiteSetting::getValue('live_stream_venue', 'Hacienda Soltepec, Huamantla');
                $address = $validated['live_stream_address'] ?? SiteSetting::getValue('live_stream_address', 'Tlaxcala, México');
                $lat = $validated['live_stream_lat'] ?? SiteSetting::getValue('live_stream_lat', '19.3182');
                $lng = $validated['live_stream_lng'] ?? SiteSetting::getValue('live_stream_lng', '-98.2375');
                $youtubeId = $validated['live_stream_youtube_id'] ?? SiteSetting::getValue('live_stream_youtube_id', 'kJQP7kiw5Fk');

                $liveEvent = \App\Models\Event::firstOrNew(['slug' => 'evento-en-vivo-actual']);
                $liveEvent->title = $title;
                $liveEvent->venue_name = $venue;
                $liveEvent->address = $address;
                $liveEvent->latitude = (float) $lat;
                $liveEvent->longitude = (float) $lng;
                $liveEvent->event_date = now();
                $liveEvent->is_live = true;
                $liveEvent->save();

                if (!empty($youtubeId)) {
                    \App\Models\EventMedia::updateOrCreate(
                        ['event_id' => $liveEvent->id, 'type' => 'youtube_live'],
                        [
                            'url' => 'https://www.youtube.com/watch?v=' . $youtubeId,
                            'external_id' => $youtubeId,
                            'caption' => 'Transmisión En Vivo',
                            'sort_order' => 1,
                        ]
                    );
                }
            }
        }

        return $this->index();
    }
}
