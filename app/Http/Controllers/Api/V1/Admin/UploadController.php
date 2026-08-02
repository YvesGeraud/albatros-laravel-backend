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

        return response()->json([
            'path' => $path,
            'url' => Storage::disk(config('filesystems.default'))->url($path),
        ], 201);
    }
}
