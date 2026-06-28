<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faculty;

class FacultyController extends Controller
{
    public function index()
    {
        return Faculty::orderBy('name')->paginate(20);
    }

    public function show(Faculty $faculty)
    {
        return $faculty;
    }

    public function store(\Illuminate\Http\Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin role required.'], 403);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'designation' => 'required|string',
            'department' => 'required|string',
            'qualification' => 'required|string',
            'email' => 'required|email|max:255',
            'phone' => 'required|string',
            'specialization' => 'nullable|array',
            'photo' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $faculty = Faculty::create($request->all());
        return response()->json($faculty, 201);
    }

    public function update(\Illuminate\Http\Request $request, Faculty $faculty)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin role required.'], 403);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'designation' => 'required|string',
            'department' => 'required|string',
            'qualification' => 'required|string',
            'email' => 'required|email|max:255',
            'phone' => 'required|string',
            'specialization' => 'nullable|array',
            'photo' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $faculty->update($request->all());
        return response()->json($faculty);
    }

    public function destroy(\Illuminate\Http\Request $request, Faculty $faculty)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin role required.'], 403);
        }

        $faculty->delete();
        return response()->json(['message' => 'Faculty member deleted successfully']);
    }
}