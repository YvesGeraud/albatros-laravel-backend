<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;

    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        $setting = static::find($key);
        return $setting ? $setting->value : $default;
    }

    public static function setValue(string $key, ?string $value): static
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    public static function formatUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $disk = config('filesystems.default');
        $url = \Illuminate\Support\Facades\Storage::disk($disk)->url($path);

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        if ($disk === 's3') {
            $awsUrl = env('AWS_URL');
            if ($awsUrl) {
                return rtrim($awsUrl, '/').'/'.ltrim($url, '/');
            }

            $bucket = env('AWS_BUCKET');
            $region = env('AWS_DEFAULT_REGION', 'us-east-1');
            $endpoint = env('AWS_ENDPOINT');

            if ($endpoint) {
                $endpointUrl = preg_match('/^https?:\/\//', $endpoint) ? $endpoint : "https://{$endpoint}";
                if ($bucket) {
                    return rtrim($endpointUrl, '/').'/'.$bucket.'/'.ltrim($url, '/');
                }
                return rtrim($endpointUrl, '/').'/'.ltrim($url, '/');
            }

            if ($bucket) {
                return "https://{$bucket}.s3.{$region}.amazonaws.com/".ltrim($url, '/');
            }
        }

        $baseUrl = env('APP_URL') ?: url('/');
        $baseUrl = preg_match('/^https?:\/\//', $baseUrl) ? $baseUrl : "https://{$baseUrl}";

        return rtrim($baseUrl, '/').'/'.ltrim($url, '/');
    }
}
