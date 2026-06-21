<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Subject;

class DepartmentController extends Controller
{
    public function index()
    {
        return Department::all();
    }

    public function show(Department $department)
    {
        $department->subjects = Subject::where('department', $department->title)
            ->orderBy('semester')
            ->orderBy('subject_code')
            ->get();
        return $department;
    }

    public function bySlug($slug)
    {
        $department = Department::where('slug', $slug)->firstOrFail();
        $department->subjects = Subject::where('department', $department->title)
            ->orderBy('semester')
            ->orderBy('subject_code')
            ->get();
        return $department;
    }

    public function store(\Illuminate\Http\Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin role required.'], 403);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:departments,slug',
            'short_title' => 'required|string',
            'description' => 'required|string',
            'overview' => 'required|string',
            'objectives' => 'nullable|array',
            'labs' => 'nullable|array',
            'achievements' => 'nullable|array',
            'career_opportunities' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $department = Department::create($request->all());
        return response()->json($department, 201);
    }

    public function update(\Illuminate\Http\Request $request, Department $department)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin role required.'], 403);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:departments,slug,' . $department->id,
            'short_title' => 'required|string',
            'description' => 'required|string',
            'overview' => 'required|string',
            'objectives' => 'nullable|array',
            'labs' => 'nullable|array',
            'achievements' => 'nullable|array',
            'career_opportunities' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $department->update($request->all());
        return response()->json($department);
    }

    public function destroy(\Illuminate\Http\Request $request, Department $department)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin role required.'], 403);
        }

        $department->delete();
        return response()->json(['message' => 'Department deleted successfully']);
    }
}