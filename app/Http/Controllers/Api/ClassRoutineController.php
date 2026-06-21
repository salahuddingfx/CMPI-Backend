<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassRoutine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClassRoutineController extends Controller
{
    public function index(Request $request)
    {
        $query = ClassRoutine::query();

        if ($request->has('department')) {
            $query->where('department', $request->department);
        }
        if ($request->has('semester')) {
            $query->where('semester', $request->semester);
        }

        return $query->orderByDesc('created_at')->get();
    }

    public function upload(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:20480',
            'department' => 'required|string',
            'semester' => 'required|string',
            'academic_year' => 'required|string',
            'title' => 'required|string',
        ]);

        $user = $request->user();
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $file = $request->file('pdf');
        $filename = 'routine_' . $request->department . '_' . $request->semester . '_' . time() . '.pdf';
        $path = $file->storeAs('routines', $filename, 'local');

        $routine = ClassRoutine::updateOrCreate(
            [
                'department' => $request->department,
                'semester' => $request->semester,
                'academic_year' => $request->academic_year,
            ],
            [
                'title' => $request->title,
                'pdf_path' => $path,
                'original_name' => $file->getClientOriginalName(),
            ]
        );

        \App\Models\Notification::create([
            'title' => 'Routine Published',
            'description' => "Exam routine for {$routine->department} ({$routine->semester}) has been published: {$routine->title}.",
            'type' => 'info',
        ]);

        return response()->json(['routine' => $routine]);
    }

    public function download(ClassRoutine $routine)
    {
        $fullPath = Storage::disk('local')->path($routine->pdf_path);

        if (!file_exists($fullPath)) {
            return response()->json(['error' => 'PDF not found'], 404);
        }

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $routine->original_name . '"',
        ]);
    }

    public function update(Request $request, ClassRoutine $routine)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'pdf' => 'nullable|file|mimes:pdf|max:20480',
            'department' => 'required|string',
            'semester' => 'required|string',
            'academic_year' => 'required|string',
            'title' => 'required|string',
        ]);

        if ($request->hasFile('pdf')) {
            $fullPath = Storage::disk('local')->path($routine->pdf_path);
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            $file = $request->file('pdf');
            $filename = 'routine_' . $request->department . '_' . $request->semester . '_' . time() . '.pdf';
            $path = $file->storeAs('routines', $filename, 'local');

            $routine->update([
                'title' => $request->title,
                'department' => $request->department,
                'semester' => $request->semester,
                'academic_year' => $request->academic_year,
                'pdf_path' => $path,
                'original_name' => $file->getClientOriginalName(),
            ]);
        } else {
            $routine->update([
                'title' => $request->title,
                'department' => $request->department,
                'semester' => $request->semester,
                'academic_year' => $request->academic_year,
            ]);
        }

        return response()->json(['routine' => $routine]);
    }

    public function destroy(Request $request, ClassRoutine $routine)
    {
        $user = $request->user();
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $fullPath = Storage::disk('local')->path($routine->pdf_path);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        $routine->delete();

        return response()->json(['deleted' => true]);
    }
}
