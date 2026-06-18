<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Notice;
use App\Models\InstituteEvent;
use App\Models\Blog;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = $request->input('q', '');

        if (strlen($q) < 2) {
            return response()->json([
                'departments' => [],
                'faculty' => [],
                'notices' => [],
                'events' => [],
                'blogs' => [],
            ]);
        }

        return response()->json([
            'departments' => Department::where('title', 'like', "%$q%")
                ->orWhere('description', 'like', "%$q%")
                ->get(),
            'faculty' => Faculty::where('name', 'like', "%$q%")
                ->orWhere('specialization', 'like', "%$q%")
                ->get(),
            'notices' => Notice::where('title', 'like', "%$q%")
                ->orWhere('summary', 'like', "%$q%")
                ->get(),
            'events' => InstituteEvent::where('title', 'like', "%$q%")
                ->orWhere('summary', 'like', "%$q%")
                ->get(),
            'blogs' => Blog::where('title', 'like', "%$q%")
                ->orWhere('excerpt', 'like', "%$q%")
                ->get(),
        ]);
    }
}