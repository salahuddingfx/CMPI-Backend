<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BtebResult;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function departmentResult(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'department' => 'required|string',
            'semester' => 'required|string',
        ]);

        $department = $request->department;
        $semester = $request->semester;

        $results = BtebResult::where('department', $department)
            ->where('semester', $semester)
            ->orderBy('roll')
            ->get();

        $pdf = Pdf::loadView('reports.department-result', [
            'department' => $department,
            'semester' => $semester,
            'results' => $results,
            'generatedAt' => now()->format('d M Y, h:i A'),
        ]);

        return $pdf->download("department-result-{$department}-{$semester}.pdf");
    }

    public function departmentResultData(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'department' => 'required|string',
            'semester' => 'required|string',
        ]);

        $results = BtebResult::where('department', $request->department)
            ->where('semester', $request->semester)
            ->orderBy('roll')
            ->get();

        $totalStudents = $results->pluck('roll')->unique()->count();
        $avgGpa = $results->where('gpa', '>', 0)->avg('gpa');
        $passCount = $results->where('gpa', '>=', 2.00)->pluck('roll')->unique()->count();
        $failCount = $totalStudents - $passCount;

        return response()->json([
            'total_records' => $results->count(),
            'total_students' => $totalStudents,
            'avg_gpa' => $avgGpa ? round($avgGpa, 2) : 0,
            'pass_count' => $passCount,
            'fail_count' => $failCount,
            'pass_rate' => $totalStudents > 0 ? round(($passCount / $totalStudents) * 100, 1) : 0,
            'results' => $results,
        ]);
    }

    public function studentTranscript(Request $request, $roll)
    {
        $results = BtebResult::where('roll', $roll)->orderBy('semester')->get();

        if ($results->isEmpty()) {
            return response()->json(['message' => 'No results found for this roll number'], 404);
        }

        $student = User::where('student_id', 'like', "%{$roll}%")->first();

        $pdf = Pdf::loadView('reports.student-transcript', [
            'roll' => $roll,
            'student' => $student,
            'results' => $results,
            'generatedAt' => now()->format('d M Y, h:i A'),
        ]);

        return $pdf->download("transcript-{$roll}.pdf");
    }

    public function studentTranscriptData(Request $request, $roll)
    {
        $results = BtebResult::where('roll', $roll)->orderBy('semester')->get();

        if ($results->isEmpty()) {
            return response()->json(['message' => 'No results found'], 404);
        }

        $student = User::where('student_id', 'like', "%{$roll}%")->first();
        $totalGpa = $results->where('gpa', '>', 0)->avg('gpa');
        $highestGpa = $results->where('gpa', '>', 0)->max('gpa');
        $lowestGpa = $results->where('gpa', '>', 0)->min('gpa');

        return response()->json([
            'roll' => $roll,
            'student' => $student,
            'results' => $results,
            'total_semesters' => $results->pluck('semester')->unique()->count(),
            'avg_gpa' => $totalGpa ? round($totalGpa, 2) : 0,
            'highest_gpa' => $highestGpa,
            'lowest_gpa' => $lowestGpa,
        ]);
    }
}
