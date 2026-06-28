<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index()
    {
        return Blog::orderByDesc('date')->paginate(15);
    }

    public function show(Blog $blog)
    {
        return $blog;
    }

    public function bySlug($slug)
    {
        $blog = Blog::where('slug', $slug)->firstOrFail();
        $blog->increment('views');
        return $blog;
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin role required.'], 403);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:blogs,slug',
            'excerpt' => 'required|string',
            'content' => 'required|string',
            'author' => 'required|string',
            'date' => 'required|date',
            'category' => 'required|string',
            'read_time' => 'required|string',
            'related_ids' => 'nullable|array',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $blog = Blog::create($request->all());
        return response()->json($blog, 201);
    }

    public function update(Request $request, Blog $blog)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin role required.'], 403);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:blogs,slug,' . $blog->id,
            'excerpt' => 'required|string',
            'content' => 'required|string',
            'author' => 'required|string',
            'date' => 'required|date',
            'category' => 'required|string',
            'read_time' => 'required|string',
            'related_ids' => 'nullable|array',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $blog->update($request->all());
        return response()->json($blog);
    }

    public function destroy(Request $request, Blog $blog)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin role required.'], 403);
        }

        $blog->delete();
        return response()->json(['message' => 'Blog deleted successfully']);
    }
}
