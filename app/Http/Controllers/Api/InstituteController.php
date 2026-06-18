<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stat;
use App\Models\Facility;
use App\Models\Achievement;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Notice;
use App\Models\InstituteEvent;
use App\Models\Blog;
use App\Models\GalleryAlbum;
use App\Models\StudentResource;
use App\Models\Faq;

class InstituteController extends Controller
{
    public function index()
    {
        return response()->json([
            'stats' => Stat::orderBy('sort_order')->get(),
            'facilities' => Facility::orderBy('sort_order')->get(),
            'achievements' => Achievement::orderBy('sort_order')->get(),
            'departments' => Department::all(),
            'faculty' => Faculty::all(),
            'notices' => Notice::orderByDesc('date')->get(),
            'events' => InstituteEvent::orderByDesc('date')->get(),
            'blogs' => Blog::orderByDesc('date')->get(),
            'albums' => GalleryAlbum::all(),
            'resources' => StudentResource::all(),
            'faqs' => Faq::orderBy('sort_order')->get(),
        ]);
    }
}