<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FileUploadController extends Controller
{
    private array $docMimes = [
        'application/pdf',
        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    private array $maxSizes = [
        'image' => 5120,
        'document' => 10240,
    ];

    private function detectType(string $mime): string
    {
        return str_starts_with($mime, 'image/') ? 'images' : 'docs';
    }

    private function storeFile($file, string $folder): array
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $type = $this->detectType($file->getClientMimeType());
        $datePath = now()->format('Y/m/d');
        $relativePath = "storage/{$folder}/{$datePath}/{$type}";
        $fullPath = public_path($relativePath);

        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        $moved = $file->move($fullPath, $filename);

        return [
            'url' => asset("{$relativePath}/{$filename}"),
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $moved->getSize(),
        ];
    }

    private function isAllowed(string $mime): bool
    {
        if (str_starts_with($mime, 'image/')) {
            return true;
        }

        return in_array($mime, $this->docMimes);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'folder' => 'required|in:users,notices,blogs,gallery,admissions,others',
        ]);

        $file = $request->file('file');

        if (!$file || !$file->isValid()) {
            $errorCode = $file ? $file->getError() : 'null';
            return response()->json([
                'message' => 'File upload failed',
                'error_code' => $errorCode,
                'error_text' => match ($errorCode) {
                    1 => 'File exceeds upload_max_filesize',
                    2 => 'File exceeds MAX_FILE_SIZE',
                    3 => 'File was only partially uploaded',
                    4 => 'No file was uploaded',
                    6 => 'Missing temp folder',
                    7 => 'Failed to write file to disk',
                    8 => 'A PHP extension stopped the upload',
                    default => 'Unknown upload error',
                },
            ], 422);
        }

        $mime = $file->getClientMimeType();

        if (!$this->isAllowed($mime)) {
            return response()->json(['message' => 'File type not allowed'], 422);
        }

        $result = $this->storeFile($file, $request->input('folder'));

        return response()->json($result);
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
            if (!$this->isAllowed($file->getClientMimeType())) {
                continue;
            }

            $uploaded[] = $this->storeFile($file, $folder);
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
