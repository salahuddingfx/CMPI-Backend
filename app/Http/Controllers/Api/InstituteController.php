<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Institute;
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
use App\Models\User;
use App\Models\Bill;
use App\Models\BtebResult;
use Illuminate\Http\Request;

class InstituteController extends Controller
{
    public function index()
    {
        $institute = Institute::firstOrCreate(['id' => 1], [
            'name' => "Cox's Bazar Model Polytechnic Institute",
            'short_name' => 'CMPI',
            'tagline' => 'Excellence in Technical Education',
            'address' => "College Road, Cox's Bazar 4750, Bangladesh",
            'phone' => '+880 341 000000',
            'email' => 'info@cmpi.edu.bd',
        ]);

        return response()->json([
            'institute' => $institute,
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

    public function update(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
            'tagline' => 'nullable|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'website' => 'nullable|string|max:255',
            'logo' => 'nullable|string|max:500',
            'eiin' => 'nullable|string|max:50',
            'established' => 'nullable|string|max:10',
        ]);

        $institute = Institute::firstOrCreate(['id' => 1]);
        $institute->update($validated);

        return response()->json($institute);
    }

    public function chartData(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $admissionsByMonth = \App\Models\Admission::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $noticesByMonth = Notice::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $blogsByMonth = Blog::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $studentsByDept = User::where('role', 'student')
            ->selectRaw('department, COUNT(*) as count')
            ->whereNotNull('department')
            ->groupBy('department')
            ->orderByDesc('count')
            ->get();

        $btebByDept = BtebResult::selectRaw('department, COUNT(*) as count')
            ->whereNotNull('department')
            ->groupBy('department')
            ->orderByDesc('count')
            ->get();

        $btebBySemester = BtebResult::selectRaw('semester, COUNT(*) as count')
            ->whereNotNull('semester')
            ->groupBy('semester')
            ->orderBy('semester')
            ->get();

        $billsByStatus = Bill::selectRaw('status, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('status')
            ->get();

        return response()->json([
            'admissions' => $admissionsByMonth,
            'notices' => $noticesByMonth,
            'blogs' => $blogsByMonth,
            'studentsByDept' => $studentsByDept,
            'btebByDept' => $btebByDept,
            'btebBySemester' => $btebBySemester,
            'billsByStatus' => $billsByStatus,
        ]);
    }
}
