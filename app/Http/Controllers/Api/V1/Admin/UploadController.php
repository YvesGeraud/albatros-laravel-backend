<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UploadRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function store(UploadRequest $request)
    {
        $file = $request->file('file');
        $folder = $request->validated('folder');
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();

        $disk = config('filesystems.default');

        try {
            $path = $file->storeAs($folder, $filename, [
                'disk' => $disk,
                'visibility' => 'public',
            ]);
            $reason = 'storeAs devolvió false';
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
}
