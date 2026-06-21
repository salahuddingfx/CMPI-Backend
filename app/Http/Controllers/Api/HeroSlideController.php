<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use Illuminate\Http\Request;

class HeroSlideController extends Controller
{
    public function index()
    {
        $slides = HeroSlide::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json($slides);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'eyebrow' => 'required|string|max:255',
            'title' => 'required|string|max:500',
            'description' => 'required|string',
            'image' => 'nullable|string|max:500',
            'cta_label' => 'nullable|string|max:255',
            'cta_href' => 'nullable|string|max:500',
            'secondary_label' => 'nullable|string|max:255',
            'secondary_href' => 'nullable|string|max:500',
            'panel_title' => 'nullable|string|max:255',
            'panel_description' => 'nullable|string',
            'stats' => 'nullable|array',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $slide = HeroSlide::create($data);
        return response()->json($slide, 201);
    }

    public function update(Request $request, HeroSlide $heroSlide)
    {
        $data = $request->validate([
            'eyebrow' => 'sometimes|string|max:255',
            'title' => 'sometimes|string|max:500',
            'description' => 'sometimes|string',
            'image' => 'nullable|string|max:500',
            'cta_label' => 'nullable|string|max:255',
            'cta_href' => 'nullable|string|max:500',
            'secondary_label' => 'nullable|string|max:255',
            'secondary_href' => 'nullable|string|max:500',
            'panel_title' => 'nullable|string|max:255',
            'panel_description' => 'nullable|string',
            'stats' => 'nullable|array',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $heroSlide->update($data);
        return response()->json($heroSlide);
    }

    public function destroy(HeroSlide $heroSlide)
    {
        $heroSlide->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
