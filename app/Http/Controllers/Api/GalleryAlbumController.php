<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GalleryAlbumController extends Controller
{
    public function index()
    {
        return GalleryAlbum::with('images')->orderBy('created_at', 'desc')->get();
    }

    public function show($id)
    {
        return GalleryAlbum::with('images')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'accent' => 'nullable|string|max:7',
            'cover' => 'nullable|string',
        ]);

        $data['count'] = 0;
        $data['accent'] ??= '#3b82f6';

        $album = GalleryAlbum::create($data);

        return response()->json($album, 201);
    }

    public function update(Request $request, $id)
    {
        $album = GalleryAlbum::findOrFail($id);

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'accent' => 'nullable|string|max:7',
            'cover' => 'nullable|string',
        ]);

        $album->update($data);

        return response()->json($album);
    }

    public function destroy($id)
    {
        $album = GalleryAlbum::findOrFail($id);
        $album->delete();

        return response()->json(['message' => 'Album deleted']);
    }

    public function uploadImages(Request $request, $id)
    {
        $album = GalleryAlbum::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'images' => 'required|array|max:20',
            'images.*' => 'required|string',
            'captions' => 'nullable|array',
            'captions.*' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $images = [];
        foreach ($request->input('images') as $i => $url) {
            $caption = $request->input('captions')[$i] ?? null;
            $images[] = $album->images()->create([
                'url' => $url,
                'caption' => $caption,
            ]);
        }

        $album->increment('count', count($images));
        $album->touch();

        return response()->json($images, 201);
    }

    public function deleteImage($albumId, $imageId)
    {
        $image = GalleryImage::where('gallery_album_id', $albumId)->findOrFail($imageId);
        $image->delete();

        GalleryAlbum::where('id', $albumId)->decrement('count');

        return response()->json(['message' => 'Image deleted']);
    }
}
