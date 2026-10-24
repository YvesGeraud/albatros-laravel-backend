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

        $path = $file->storeAs($folder, $filename, [
            'disk' => config('filesystems.default'),
            'visibility' => 'public',
        ]);

        if (! is_string($path) || $path === '') {
            \Log::error('Upload failed: storeAs returned false', [
                'folder' => $folder,
                'disk' => config('filesystems.default'),
                'error' => $file->getErrorMessage(),
            ]);

            return response()->json([
                'message' => 'No se pudo guardar el archivo en el servidor (revisa permisos de storage/ o espacio en disco).',
            ], 500);
        }

        return response()->json([
            'path' => $path,
            'url' => \App\Models\SiteSetting::formatUrl($path),
        ], 201);
    }
}
