<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UploadRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    private const MAX_IMAGE_DIMENSION = 1600;
    private const WEBP_QUALITY = 82;
    private const JPEG_QUALITY = 84;

    public function store(UploadRequest $request)
    {
        $file = $request->file('file');
        $folder = $request->validated('folder');
        $disk = config('filesystems.default');

        try {
            $compressed = $this->compressImageIfApplicable($file);
            if ($compressed !== null) {
                $filename = Str::uuid().'.'.$compressed['extension'];
                $path = "{$folder}/{$filename}";
                $stored = Storage::disk($disk)->put($path, $compressed['contents'], [
                    'visibility' => 'public',
                    'ContentType' => $compressed['mime'],
                ]);
                if (! $stored) {
                    $path = false;
                }
            } else {
                $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
                $path = $file->storeAs($folder, $filename, [
                    'disk' => $disk,
                    'visibility' => 'public',
                ]);
            }
            $reason = 'store devolvió false';
        } catch (\Throwable $e) {
            $path = false;
            $reason = $e->getMessage();
        }

        if (! is_string($path) || $path === '') {
            try {
                \Log::error('Upload failed', ['folder' => $folder, 'disk' => $disk, 'reason' => $reason]);
            } catch (\Throwable) {
            }

            return response()->json([
                'message' => "No se pudo guardar el archivo (disco: {$disk}). Causa: {$reason}",
            ], 500);
        }

        return response()->json([
            'path' => $path,
            'url' => \App\Models\SiteSetting::formatUrl($path),
        ], 201);
    }

    /**
     * Resize and compress raster images (JPG, PNG, WebP) using GD so uploads
     * are lightweight and load fast from the bucket. Videos, GIFs, or
     * environments without GD fall back to storing the original file.
     *
     * @return array{contents: string, extension: string, mime: string}|null
     */
    private function compressImageIfApplicable(UploadedFile $file): ?array
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $mime = strtolower((string) $file->getMimeType());
        if (! in_array($mime, ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'], true)) {
            return null;
        }

        $realPath = $file->getRealPath();
        if (! $realPath || ! is_readable($realPath)) {
            return null;
        }

        $raw = @file_get_contents($realPath);
        if ($raw === false || $raw === '') {
            return null;
        }

        $src = @imagecreatefromstring($raw);
        if (! $src) {
            return null;
        }

        // Fix EXIF orientation for JPEGs taken on phones
        if (in_array($mime, ['image/jpeg', 'image/jpg'], true) && function_exists('exif_read_data')) {
            $exif = @exif_read_data($realPath);
            $orientation = $exif['Orientation'] ?? null;
            if ($orientation === 3) {
                $rotated = @imagerotate($src, 180, 0);
                if ($rotated) {
                    imagedestroy($src);
                    $src = $rotated;
                }
            } elseif ($orientation === 6) {
                $rotated = @imagerotate($src, -90, 0);
                if ($rotated) {
                    imagedestroy($src);
                    $src = $rotated;
                }
            } elseif ($orientation === 8) {
                $rotated = @imagerotate($src, 90, 0);
                if ($rotated) {
                    imagedestroy($src);
                    $src = $rotated;
                }
            }
        }

        $width = imagesx($src);
        $height = imagesy($src);

        if ($width <= 0 || $height <= 0) {
            imagedestroy($src);
            return null;
        }

        $maxDim = self::MAX_IMAGE_DIMENSION;
        if ($width > $maxDim || $height > $maxDim) {
            $ratio = min($maxDim / $width, $maxDim / $height);
            $newWidth = max(1, (int) round($width * $ratio));
            $newHeight = max(1, (int) round($height * $ratio));

            $dst = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($src);
            $canvas = $dst;
        } else {
            imagepalettetotruecolor($src);
            imagesavealpha($src, true);
            $canvas = $src;
        }

        ob_start();
        if (function_exists('imagewebp')) {
            $ok = @imagewebp($canvas, null, self::WEBP_QUALITY);
            $extension = 'webp';
            $outMime = 'image/webp';
        } else {
            $ok = @imagejpeg($canvas, null, self::JPEG_QUALITY);
            $extension = 'jpg';
            $outMime = 'image/jpeg';
        }
        $contents = ob_get_clean();
        imagedestroy($canvas);

        if (! $ok || ! is_string($contents) || $contents === '') {
            return null;
        }

        // Only use the compressed version if it is actually smaller or was resized
        if (strlen($contents) >= strlen($raw) && $width <= $maxDim && $height <= $maxDim) {
            return null;
        }

        return [
            'contents' => $contents,
            'extension' => $extension,
            'mime' => $outMime,
        ];
    }
}
