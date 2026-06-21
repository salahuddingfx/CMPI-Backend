<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InstituteResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser as PdfParser;

class InstituteResultController extends Controller
{
    public function search(Request $request)
    {
        $request->validate(['roll' => 'required|string|min:6|max:6']);

        $results = InstituteResult::where('roll', $request->roll)
            ->orderBy('semester')
            ->get();

        return response()->json($results);
    }

    public function stats()
    {
        $total = InstituteResult::count();
        $rolls = InstituteResult::distinct('roll')->count('roll');
        $sems = InstituteResult::select('semester')->distinct()->pluck('semester');
        $years = InstituteResult::select('academic_year')->distinct()->pluck('academic_year');
        $referred = InstituteResult::where('status', 'Referred')->count();

        return response()->json([
            'total_records' => $total,
            'distinct_rolls' => $rolls,
            'semesters' => $sems,
            'academic_years' => $years,
            'referred_count' => $referred,
        ]);
    }

    public function uploadCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
            'semester' => 'required|string',
            'academic_year' => 'required|string',
        ]);

        $user = $request->user();
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $file = $request->file('file');
        $semester = $request->semester;
        $academicYear = $request->academic_year;

        $extension = strtolower($file->getClientOriginalExtension());

        $rows = [];
        if (in_array($extension, ['xlsx', 'xls'])) {
            $rows = $this->parseExcel($file->getRealPath());
        } else {
            $rows = $this->parseCsv($file->getRealPath());
        }

        if (empty($rows)) {
            return response()->json(['error' => 'No data found in file'], 422);
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $i => $row) {
                $roll = trim($row['roll'] ?? '');
                if (strlen($roll) !== 6 || !ctype_digit($roll)) {
                    $errors[] = "Row " . ($i + 2) . ": Invalid roll '$roll'";
                    $skipped++;
                    continue;
                }

                $failedRaw = trim($row['failed_subjects'] ?? '');
                $referredSubjects = null;
                $status = 'Passed';

                if ($failedRaw !== '') {
                    $referredSubjects = array_values(array_filter(array_map('trim', explode(',', $failedRaw))));
                    $status = 'Referred';
                }

                InstituteResult::updateOrCreate(
                    ['roll' => $roll, 'semester' => $semester, 'academic_year' => $academicYear],
                    [
                        'status' => $status,
                        'referred_subjects' => $referredSubjects,
                        'raw_text' => $failedRaw,
                    ]
                );
                $imported++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Import failed: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ]);
    }

    public function uploadPdf(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:20480',
            'semester' => 'required|string',
            'academic_year' => 'required|string',
        ]);

        $user = $request->user();
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $semester = $request->semester;
        $academicYear = $request->academic_year;

        $pdfParser = new PdfParser();
        $pdf = $pdfParser->parseFile($request->file('pdf')->getRealPath());
        $pages = $pdf->getPages();

        $imported = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($pages as $page) {
                $pageText = $page->getText();

                $cmpiStartIndex = strpos($pageText, "74026");
                if ($cmpiStartIndex === false) continue;

                $cmpiText = substr($pageText, $cmpiStartIndex);
                if (preg_match('/\b\d{5}\s*-\s*/', substr($cmpiText, 10), $nextInstMatch, PREG_OFFSET_CAPTURE)) {
                    $cmpiText = substr($cmpiText, 0, $nextInstMatch[0][1] + 10);
                }

                // Parse referred: roll { subject_codes }
                preg_match_all('/\b(\d{6})\s*\{\s*([^}]+)\s*\}/', $cmpiText, $referredMatches, PREG_SET_ORDER);
                foreach ($referredMatches as $match) {
                    $roll = $match[1];
                    $contentStr = $match[2];

                    preg_match_all('/ref_sub\s*:\s*([^\}]+)/i', $contentStr, $refSubMatch);
                    $referredSubjects = null;
                    $status = 'Referred';

                    if (!empty($refSubMatch[1])) {
                        $refRaw = $refSubMatch[1][0];
                        preg_match_all('/\b\d{5,6}(?:\([^)]+\))?\b/', $refRaw, $codeMatches);
                        $referredSubjects = array_values(array_filter(array_map('trim', $codeMatches[0] ?? [])));
                    } else {
                        preg_match_all('/\b\d{5,6}(?:\([^)]+\))?\b/', $contentStr, $codeMatches);
                        $codes = array_values(array_filter(array_map('trim', $codeMatches[0] ?? [])));
                        if (!empty($codes)) {
                            $referredSubjects = $codes;
                        } else {
                            $status = 'Passed';
                        }
                    }

                    InstituteResult::updateOrCreate(
                        ['roll' => $roll, 'semester' => $semester, 'academic_year' => $academicYear],
                        [
                            'status' => $status,
                            'referred_subjects' => $referredSubjects,
                            'raw_text' => $match[0],
                        ]
                    );
                    $imported++;
                }

                // Parse passed: roll ( gpa ) or roll ( content )
                preg_match_all('/\b(\d{6})\s*\(\s*([^)]+)\s*\)/', $cmpiText, $passedMatches, PREG_SET_ORDER);
                foreach ($passedMatches as $match) {
                    $roll = $match[1];

                    InstituteResult::updateOrCreate(
                        ['roll' => $roll, 'semester' => $semester, 'academic_year' => $academicYear],
                        [
                            'status' => 'Passed',
                            'referred_subjects' => null,
                            'raw_text' => $match[0],
                        ]
                    );
                    $imported++;
                }

                // Parse IRR/cgpa format: roll cgpa: X.XX (gpa8: X.XX, ...)
                preg_match_all('/\b(\d{6})\s+cgpa:\s*([2-4]\.\d{2})\s*\(\s*([\s\S]+?)\s*\)/', $cmpiText, $cgpaMatches, PREG_SET_ORDER);
                foreach ($cgpaMatches as $match) {
                    $roll = $match[1];
                    $innerContent = $match[3];

                    $hasReferred = (stripos($innerContent, 'ref') !== false);
                    $status = $hasReferred ? 'Referred' : 'Passed';
                    $referredSubjects = null;

                    if ($hasReferred) {
                        preg_match_all('/ref_sub\s*:\s*([^\)}]+)/i', $innerContent, $refSubMatch);
                        if (!empty($refSubMatch[1])) {
                            preg_match_all('/\b\d{5,6}(?:\([^)]+\))?\b/', $refSubMatch[1][0], $codeMatches);
                            $referredSubjects = array_values(array_filter(array_map('trim', $codeMatches[0] ?? [])));
                        }
                    }

                    InstituteResult::updateOrCreate(
                        ['roll' => $roll, 'semester' => $semester, 'academic_year' => $academicYear],
                        [
                            'status' => $status,
                            'referred_subjects' => $referredSubjects,
                            'raw_text' => $match[0],
                        ]
                    );
                    $imported++;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Import failed: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
            'pages_processed' => count($pages),
        ]);
    }

    public function manual(Request $request)
    {
        $request->validate([
            'roll' => 'required|string|size:6|digits:6',
            'semester' => 'required|string',
            'academic_year' => 'required|string',
            'status' => 'required|in:Passed,Referred',
            'referred_subjects' => 'nullable|array',
            'referred_subjects.*' => 'string',
        ]);

        $user = $request->user();
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $referredSubjects = null;
        if ($request->status === 'Referred' && !empty($request->referred_subjects)) {
            $referredSubjects = $request->referred_subjects;
        }

        $result = InstituteResult::updateOrCreate(
            [
                'roll' => $request->roll,
                'semester' => $request->semester,
                'academic_year' => $request->academic_year,
            ],
            [
                'status' => $request->status,
                'referred_subjects' => $referredSubjects,
                'raw_text' => 'manual_entry',
            ]
        );

        return response()->json(['result' => $result]);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'roll' => 'sometimes|string|size:6|digits:6',
            'semester' => 'sometimes|string',
            'academic_year' => 'sometimes|string',
            'status' => 'sometimes|in:Passed,Referred',
            'referred_subjects' => 'nullable|array',
            'referred_subjects.*' => 'string',
        ]);

        $result = InstituteResult::findOrFail($id);

        $data = $request->only(['roll', 'semester', 'academic_year', 'status']);
        if ($request->has('referred_subjects')) {
            $data['referred_subjects'] = $request->referred_subjects;
        }

        $result->update($data);

        return response()->json(['result' => $result]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $result = InstituteResult::findOrFail($id);
        $result->delete();

        return response()->json(['deleted' => true]);
    }

    private function parseCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');
        if (!$handle) return $rows;

        $header = fgetcsv($handle);
        if (!$header) return $rows;

        $header = array_map('strtolower', array_map('trim', $header));
        $rollIdx = array_search('roll', $header);
        $failIdx = array_search('failed_subjects', $header);

        if ($rollIdx === false) return $rows;

        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = [
                'roll' => $row[$rollIdx] ?? '',
                'failed_subjects' => ($failIdx !== false) ? ($row[$failIdx] ?? '') : '',
            ];
        }
        fclose($handle);
        return $rows;
    }

    private function parseExcel(string $path): array
    {
        $rows = [];
        if (!file_exists($path)) return $rows;

        $csvData = file_get_contents($path);
        if ($csvData === false) return $rows;

        $lines = explode("\n", $csvData);
        if (empty($lines)) return $rows;

        $header = array_map('strtolower', array_map('trim', str_getcsv(array_shift($lines))));
        $rollIdx = array_search('roll', $header);
        $failIdx = array_search('failed_subjects', $header);

        if ($rollIdx === false) return $rows;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;
            $row = str_getcsv($line);
            $rows[] = [
                'roll' => $row[$rollIdx] ?? '',
                'failed_subjects' => ($failIdx !== false) ? ($row[$failIdx] ?? '') : '',
            ];
        }
        return $rows;
    }
}
