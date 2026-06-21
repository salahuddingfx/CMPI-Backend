<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Subject::query();

        if ($request->has('department')) {
            $query->where('department', $request->department);
        }

        if ($request->has('semester')) {
            $query->where('semester', $request->semester);
        }

        return $query->orderBy('department')->orderBy('semester')->orderBy('subject_code')->get();
    }

    /**
     * Lookup subjects by codes (batch).
     * GET /api/subjects/lookup?codes=26442,26444,26521
     */
    public function lookup(Request $request)
    {
        $codes = array_filter(explode(',', $request->input('codes', '')));

        if (empty($codes)) {
            return response()->json(['error' => 'codes parameter required (comma-separated)'], 400);
        }

        $subjects = Subject::whereIn('subject_code', $codes)->get();

        // Format as { code: { name, department, credit, ... } }
        $result = [];
        foreach ($subjects as $s) {
            $result[$s->subject_code] = [
                'name' => $s->subject_name,
                'department' => $s->department,
                'technology_code' => $s->technology_code,
                'semester' => $s->semester,
                'credit' => $s->credit,
                'theory_marks' => $s->theory_marks,
                'practical_marks' => $s->practical_marks,
                'total_marks' => $s->total_marks,
            ];
        }

        return response()->json($result);
    }

    /**
     * Detect department from a list of subject codes.
     * GET /api/subjects/detect-department?codes=26442,26444
     */
    public function detectDepartment(Request $request)
    {
        $codes = array_filter(explode(',', $request->input('codes', '')));

        if (empty($codes)) {
            return response()->json(['department' => 'General Technology']);
        }

        // Count occurrences of each department (skip shared/general)
        $counts = [];
        $subjects = Subject::whereIn('subject_code', $codes)->get();

        foreach ($subjects as $s) {
            $dept = $s->department;
            // Skip if this subject appears in multiple departments (shared)
            $deptCount = Subject::where('subject_code', $s->subject_code)->distinct('department')->count('department');
            if ($deptCount > 1) continue; // shared code, skip

            $counts[$dept] = ($counts[$dept] ?? 0) + 1;
        }

        if (empty($counts)) {
            // Fallback: just use the most common department from the codes
            $fallback = Subject::whereIn('subject_code', $codes)
                ->selectRaw('department, count(*) as cnt')
                ->groupBy('department')
                ->orderByDesc('cnt')
                ->first();
            return response()->json(['department' => $fallback?->department ?? 'General Technology']);
        }

        // Return department with highest count
        arsort($counts);
        $detected = array_key_first($counts);

        return response()->json(['department' => $detected]);
    }

    /**
     * Get all subjects formatted as a frontend dictionary.
     * GET /api/subjects/dictionary
     */
    public function dictionary()
    {
        $subjects = Subject::all();

        $result = [];
        foreach ($subjects as $s) {
            // Check if this code appears in multiple departments (shared)
            $deptCount = Subject::where('subject_code', $s->subject_code)
                ->distinct('department')
                ->count('department');

            $result[$s->subject_code] = [
                'name' => $s->subject_name,
                'dept' => $deptCount > 1 ? 'Shared' : $s->department,
            ];
        }

        return response()->json($result);
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin role required.'], 403);
        }

        $data = $request->validate([
            'department' => 'required|string',
            'semester' => 'required|string',
            'subject_code' => 'required|string',
            'subject_name' => 'required|string',
            'credit' => 'required|numeric',
            'technology_code' => 'nullable|string',
            'technology_name' => 'nullable|string',
            'theory_marks' => 'nullable|numeric',
            'practical_marks' => 'nullable|numeric',
            'total_marks' => 'nullable|numeric',
        ]);

        $subject = Subject::create($data);

        return response()->json($subject, 201);
    }

    public function update(Request $request, Subject $subject)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin role required.'], 403);
        }

        $data = $request->validate([
            'department' => 'sometimes|string',
            'semester' => 'sometimes|string',
            'subject_code' => 'sometimes|string',
            'subject_name' => 'sometimes|string',
            'credit' => 'sometimes|numeric',
            'technology_code' => 'sometimes|nullable|string',
            'technology_name' => 'sometimes|nullable|string',
            'theory_marks' => 'sometimes|nullable|numeric',
            'practical_marks' => 'sometimes|nullable|numeric',
            'total_marks' => 'sometimes|nullable|numeric',
        ]);

        $subject->update($data);

        return response()->json($subject);
    }

    public function destroy(Request $request, Subject $subject)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin role required.'], 403);
        }

        $subject->delete();

        return response()->json(['message' => 'Subject deleted successfully']);
    }
}
