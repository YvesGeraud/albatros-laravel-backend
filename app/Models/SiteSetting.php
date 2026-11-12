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

    private const UPLOAD_FOLDERS = [
        'products',
        'combos',
        'events',
        'testimonials',
        'hero',
        'about',
        'gallery',
        'trayectoria',
        'paquetes',
        'cms',
    ];

    /**
     * If $value is a stored URL from our own bucket or local storage (even an
     * expired S3 presigned URL with ?X-Amz-... query parameters), extract its
     * clean relative storage path (e.g. "gallery/uuid.jpg"). External URLs
     * (like Unsplash) are returned untouched.
     */
    public static function extractStoragePath(?string $value): ?string
    {
        if (! $value) {
            return $value;
        }

        if (! str_starts_with($value, 'http://') && ! str_starts_with($value, 'https://')) {
            return ltrim($value, '/');
        }

        $foldersPattern = implode('|', self::UPLOAD_FOLDERS);
        $root = trim((string) config('filesystems.disks.s3.root', 'albatros'), '/');
        $bucket = trim((string) config('filesystems.disks.s3.bucket', ''), '/');

        $urlPath = parse_url($value, PHP_URL_PATH);
        if (! is_string($urlPath) || $urlPath === '') {
            return $value;
        }

        $urlPath = rawurldecode($urlPath);
        // Normalize multiple consecutive slashes (e.g. "///albatros///gallery///file.jpg")
        $urlPath = preg_replace('#/+#', '/', $urlPath);

        $isSignedS3 = str_contains($value, 'X-Amz-Signature') || str_contains($value, 'X-Amz-Algorithm') || str_contains($value, 'AWSAccessKeyId');
        $isLocalStorage = str_contains($urlPath, '/storage/');
        $hasRootPrefix = $root !== '' && preg_match('#(?:^|/)' . preg_quote($root, '#') . '/(' . $foldersPattern . ')/#i', $urlPath);
        $hasBucketPrefix = ($bucket !== '' && str_contains($value, $bucket)) || str_contains($value, 'storageapi.dev');

        if ($isSignedS3 || $isLocalStorage || $hasRootPrefix || $hasBucketPrefix) {
            if (preg_match('#(?:^|/)(?:storage/)?(?:' . preg_quote($root, '#') . '/)?((' . $foldersPattern . ')/[^?\#]+)$#i', $urlPath, $matches)) {
                return $matches[1];
            }
        }

        return $value;
    }

    /**
     * Recursively normalize any stored media URLs inside a decoded JSON array
     * into relative storage paths before saving to the database.
     */
    public static function normalizeMediaPaths(mixed $data): mixed
    {
        if (! is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = static::normalizeMediaPaths($value);
            } elseif (is_string($value) && in_array($key, ['image', 'url', 'avatar', 'photo', 'video'], true)) {
                $data[$key] = static::extractStoragePath($value);
            }
        }

        return $data;
    }

    /**
     * Recursively format any relative storage paths (or previously saved
     * expired S3 URLs) inside a decoded JSON array into fresh URLs.
     */
    public static function formatMediaUrls(mixed $data): mixed
    {
        if (! is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = static::formatMediaUrls($value);
            } elseif (is_string($value) && in_array($key, ['image', 'url', 'avatar', 'photo', 'video'], true) && $value !== '') {
                $data[$key] = static::formatUrl($value);
            }
        }

        return $data;
    }

    public static function formatUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $path = static::extractStoragePath($path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $disk = config('filesystems.default');

        if ($disk === 's3') {
            try {
                $cacheKey = 's3_signed_url:' . md5($path);
                $signedUrl = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addDays(6), function () use ($path) {
                    return \Illuminate\Support\Facades\Storage::disk('s3')->temporaryUrl($path, now()->addDays(7));
                });
                if ($signedUrl) {
                    return $signedUrl;
                }
            } catch (\Throwable $e) {
                // Fallback if temporaryUrl is not supported
            }
        }

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
