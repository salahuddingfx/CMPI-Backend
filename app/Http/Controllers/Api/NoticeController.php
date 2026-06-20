<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NoticeController extends Controller
{
    public function index()
    {
        return Notice::orderByDesc('date')->get();
    }

    public function show(Notice $notice)
    {
        return $notice;
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin role required.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'date' => 'required|date',
            'summary' => 'required|string',
            'details' => 'required|string',
            'file_url' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $notice = Notice::create($request->all());
        return response()->json($notice, 201);
    }

    public function update(Request $request, Notice $notice)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin role required.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'date' => 'required|date',
            'summary' => 'required|string',
            'details' => 'required|string',
            'file_url' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $notice->update($request->all());
        return response()->json($notice);
    }

    public function destroy(Request $request, Notice $notice)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin role required.'], 403);
        }

        $notice->delete();
        return response()->json(['message' => 'Notice deleted successfully']);
    }
}