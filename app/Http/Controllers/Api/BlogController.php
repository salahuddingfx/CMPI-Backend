<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;

class BlogController extends Controller
{
    public function index()
    {
        return Blog::orderByDesc('date')->get();
    }

    public function show(Blog $blog)
    {
        return $blog;
    }

    public function bySlug($slug)
    {
        return Blog::where('slug', $slug)->firstOrFail();
    }
}