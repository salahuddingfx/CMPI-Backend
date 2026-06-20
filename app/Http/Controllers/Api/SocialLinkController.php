<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use Illuminate\Http\Request;

class SocialLinkController extends Controller
{
    public function index()
    {
        return response()->json(
            SocialLink::orderBy('sort_order')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|string|max:50',
            'url' => 'required|url|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $socialLink = SocialLink::create($validated);

        return response()->json($socialLink, 201);
    }

    public function update(Request $request, SocialLink $socialLink)
    {
        $validated = $request->validate([
            'platform' => 'required|string|max:50',
            'url' => 'required|url|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $socialLink->update($validated);

        return response()->json($socialLink);
    }

    public function destroy(SocialLink $socialLink)
    {
        $socialLink->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
