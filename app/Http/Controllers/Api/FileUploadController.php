<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FileUploadController extends Controller
{
    private array $allowedMimes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
        'application/pdf',
        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    private array $maxSizes = [
        'image' => 5120,   // 5MB
        'document' => 10240, // 10MB
    ];

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'folder' => 'required|in:users,notices,blogs,gallery,admissions,others',
        ]);

        $file = $request->file('file');
        $folder = $request->input('folder');

        if (!in_array($file->getMimeType(), $this->allowedMimes)) {
            return response()->json(['message' => 'File type not allowed'], 422);
        }

        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->move(public_path("storage/{$folder}"), $filename);

        $url = "/storage/{$folder}/{$filename}";

        return response()->json([
            'url' => $url,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'folder' => $folder,
        ]);
    }

    public function uploadMultiple(Request $request)
    {
        $request->validate([
            'files' => 'required|array|max:10',
            'files.*' => 'file|max:10240',
            'folder' => 'required|in:users,notices,blogs,gallery,admissions,others',
        ]);

        $folder = $request->input('folder');
        $uploaded = [];

        foreach ($request->file('files') as $file) {
            if (!in_array($file->getMimeType(), $this->allowedMimes)) {
                continue;
            }

            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path("storage/{$folder}"), $filename);

            $uploaded[] = [
                'url' => "/storage/{$folder}/{$filename}",
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ];
        }

        return response()->json(['files' => $uploaded, 'count' => count($uploaded)]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = $request->input('path');
        $realPublic = realpath(public_path());
        $fullPath = realpath(public_path($path));

        if ($fullPath === false || !str_starts_with($fullPath, $realPublic)) {
            return response()->json(['message' => 'Invalid path'], 400);
        }

        if (file_exists($fullPath)) {
            unlink($fullPath);
            return response()->json(['message' => 'File deleted']);
        }

        return response()->json(['message' => 'File not found'], 404);
    }
}
