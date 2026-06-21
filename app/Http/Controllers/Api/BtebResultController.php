<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BtebResult;
use App\Models\ImportJob;
use App\Jobs\ProcessBtebDriveImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Smalot\PdfParser\Parser;

class BtebResultController extends Controller
{
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'roll' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Roll number is required'], 422);
        }

        $roll = $request->query('roll');
        $results = BtebResult::where('roll', $roll)
            ->orderBy('semester')
            ->get();

        return response()->json($results);
    }

    public function import(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin role required.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'results' => 'required|array',
            'results.*.roll' => 'required|string',
            'results.*.department' => 'required|string',
            'results.*.semester' => 'required|string',
            'results.*.regulation' => 'required|string',
            'results.*.holding_year' => 'required|string',
            'results.*.status' => 'required|string|in:Passed,Referred',
            'results.*.gpa' => 'nullable|numeric',
            'results.*.referred_subjects' => 'nullable|array',
            'results.*.raw_text' => 'nullable|string',
            'results.*.center_code' => 'nullable|string',
            'results.*.institute_name' => 'nullable|string',
            'results.*.exam_type' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid results payload data',
                'errors' => $validator->errors()
            ], 422);
        }

        $results = $request->input('results', []);

        DB::beginTransaction();
        try {
            foreach ($results as $result) {
                BtebResult::updateOrCreate(
                    [
                        'roll' => $result['roll'],
                        'semester' => $result['semester'],
                        'regulation' => $result['regulation'],
                    ],
                    [
                        'center_code' => $result['center_code'] ?? null,
                        'institute_name' => $result['institute_name'] ?? null,
                        'department' => $result['department'],
                        'holding_year' => $result['holding_year'],
                        'gpa' => $result['gpa'] ?? null,
                        'status' => $result['status'],
                        'referred_subjects' => $result['referred_subjects'] ?? null,
                        'raw_text' => $result['raw_text'] ?? null,
                        'exam_type' => $result['exam_type'] ?? 'regular',
                    ]
                );
            }
            DB::commit();

            return response()->json([
                'message' => 'Successfully imported BTEB results',
                'count' => count($results)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to import results due to a database error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function importFromDrive(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin role required.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'drive_url' => 'required|string',
            'semester' => 'nullable|string',
            'regulation' => 'nullable|string',
            'holding_year' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $importJob = ImportJob::create([
            'drive_url' => $request->input('drive_url'),
            'semester' => $request->input('semester') ?? 'auto',
            'regulation' => $request->input('regulation') ?? 'auto',
            'holding_year' => $request->input('holding_year') ?? 'auto',
            'status' => 'pending',
        ]);

        ProcessBtebDriveImport::dispatch(
            $importJob,
            $request->input('drive_url'),
            $request->input('semester'),
            $request->input('regulation'),
            $request->input('holding_year')
        );

        return response()->json([
            'message' => 'Import job started',
            'job_id' => $importJob->id,
            'status' => 'pending',
        ]);
    }

    public function importStatus(Request $request, string $jobId)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $job = ImportJob::find($jobId);
        if (!$job) {
            return response()->json(['message' => 'Import job not found'], 404);
        }

        return response()->json([
            'id' => $job->id,
            'status' => $job->status,
            'total_files' => $job->total_files,
            'processed_files' => $job->processed_files,
            'total_results' => $job->total_results,
            'error_log' => $job->error_log,
            'created_at' => $job->created_at,
            'updated_at' => $job->updated_at,
        ]);
    }

    public function uploadPdf(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin role required.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'pdf' => 'required|file|mimes:pdf|max:10240',
            'semester' => 'nullable|string|in:1st,2nd,3rd,4th,5th,6th,7th,8th',
            'regulation' => 'required|string',
            'holding_year' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $file = $request->file('pdf');
        $semester = $request->input('semester') ?? '1st';
        $regulation = $request->input('regulation');
        $holdingYear = $request->input('holding_year');

        $pdfContent = file_get_contents($file->getRealPath());
        $pdfParser = new Parser();
        $pdf = $pdfParser->parseContent($pdfContent);

        $results = [];
        $lastDetectedDept = "Computer Science & Technology";

        foreach ($pdf->getPages() as $page) {
            $pageText = $page->getText();

            // Split page into institute sections by center code boundaries
            preg_match_all('/\b(\d{5})\s*(?:-\s*([^\n]*))?/', $pageText, $codeMatches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

            if (empty($codeMatches)) {
                $sections = [['text' => $pageText, 'center_code' => null, 'institute_name' => null]];
            } else {
                $sections = [];
                for ($si = 0; $si < count($codeMatches); $si++) {
                    $centerCode = $codeMatches[$si][1][0];
                    $instName = trim($codeMatches[$si][2][0] ?? '');
                    $startOff = $codeMatches[$si][0][1];
                    $endOff = ($si + 1 < count($codeMatches)) ? $codeMatches[$si + 1][0][1] : strlen($pageText);
                    $sections[] = [
                        'text' => substr($pageText, $startOff, $endOff - $startOff),
                        'center_code' => $centerCode,
                        'institute_name' => $instName,
                    ];
                }
            }

            foreach ($sections as $section) {
                $centerCode = $section['center_code'];
                $instName = $section['institute_name'];
                $cmpiText = $section['text'];

                if (preg_match('/\b\d{5}\s*-\s*/', substr($cmpiText, 10), $nextInstMatch, PREG_OFFSET_CAPTURE)) {
                    $cmpiText = substr($cmpiText, 0, $nextInstMatch[0][1] + 10);
                }

                // Detect semester from text headers
                $semesterChunks = $this->splitBySemesterHeader($cmpiText, $semester);

            foreach ($semesterChunks as $chunk) {
                $chunkText = $chunk['text'];
                $chunkSemester = $chunk['semester'];

                preg_match_all('/\b(\d{2,5})\s*-\s*([a-zA-Z\s&]+Technology|[a-zA-Z\s&]+Engineering)/', $chunkText, $techMatches, PREG_OFFSET_CAPTURE);

                $techBlocks = [];
                if (empty($techMatches[0])) {
                    $techBlocks[] = ['dept' => $lastDetectedDept, 'text' => $chunkText];
                } else {
                    $matchesCount = count($techMatches[0]);
                    for ($i = 0; $i < $matchesCount; $i++) {
                        $start = $techMatches[0][$i][1];
                        $end = ($i + 1 < $matchesCount) ? $techMatches[0][$i + 1][1] : strlen($chunkText);
                        $deptName = trim($techMatches[2][$i][0]);
                        if (stripos($deptName, "computer") !== false) {
                            $deptName = "Computer Science & Technology";
                        } elseif (stripos($deptName, "civil") !== false) {
                            $deptName = "Civil Technology";
                        } elseif (stripos($deptName, "electrical") !== false) {
                            $deptName = "Electrical Technology";
                        }
                        $techBlocks[] = [
                            'dept' => $deptName,
                            'text' => substr($chunkText, $start, $end - $start)
                        ];
                    }
                }

                foreach ($techBlocks as $block) {
                    $dept = $block['dept'];
                    $blockText = $block['text'];

                    // IRR/cgpa format: 593818 cgpa: 3.36 (gpa8: 3.75, gpa7: 3.34, gpa6: 3.42, gpa5: 3.04, gpa4: 2.88)
                    // Use [\\s\\S] instead of . to match across line breaks in PDF text
                    preg_match_all('/\b(\d{6})\s+cgpa:\s*([2-4]\.\d{2})\s*\(\s*([\s\S]+?)\s*\)/', $blockText, $cgpaMatches, PREG_SET_ORDER);
                    foreach ($cgpaMatches as $match) {
                        $roll = $match[1];
                        $cgpa = (float)$match[2];
                        $innerContent = $match[3];

                        $cgpaSemGpas = preg_match_all('/gpa(\d)\s*:\s*([2-4]\.\d{2})/i', $innerContent, $cgpaSemMatches, PREG_SET_ORDER);
                        if ($cgpaSemGpas > 1) {
                            foreach ($cgpaSemMatches as $sg) {
                                $semDigit = $sg[1];
                                $gpaVal = (float)$sg[2];
                                $suffix = match ((int)$semDigit) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };
                                $semLabel = $semDigit . $suffix;
                                $results[] = [
                                    'roll' => $roll,
                                    'department' => $dept,
                                    'semester' => $semLabel,
                                    'regulation' => $regulation,
                                    'holding_year' => $holdingYear,
                                    'gpa' => $gpaVal,
                                    'status' => 'Passed',
                                    'referred_subjects' => null,
                                    'raw_text' => "gpa{$semDigit}: {$gpaVal}",
                                    'center_code' => $centerCode ?? null,
                                    'institute_name' => $instName ?? null,
                                    'exam_type' => 'regular',
                                ];
                            }
                        } else {
                            $results[] = [
                                'roll' => $roll,
                                'department' => $dept,
                                'semester' => $chunkSemester,
                                'regulation' => $regulation,
                                'holding_year' => $holdingYear,
                                'gpa' => $cgpa,
                                'status' => 'Passed',
                                'referred_subjects' => null,
                                'raw_text' => $match[0],
                                'center_code' => $centerCode ?? null,
                                'institute_name' => $instName ?? null,
                                'exam_type' => 'regular',
                            ];
                        }
                        $lastDetectedDept = ($dept !== "Auto Detect" && $dept !== "General Technology") ? $dept : $lastDetectedDept;
                    }

                    preg_match_all('/\b(\d{6})\s*\(\s*([^)]+)\s*\)/', $blockText, $passedMatches, PREG_SET_ORDER);
                    foreach ($passedMatches as $match) {
                        $roll = $match[1];
                        $contentStr = $match[2];
                        $gpa = null;
                        if (preg_match('/^[2-4]\.\d{2}$/', trim($contentStr))) {
                            $gpa = (float)trim($contentStr);
                        } elseif (preg_match('/([2-4]\.\d{2})/', $contentStr, $gpaMatches)) {
                            $gpa = (float)$gpaMatches[1];
                        }
                        $results[] = [
                            'roll' => $roll,
                            'department' => $dept,
                            'semester' => $chunkSemester,
                            'regulation' => $regulation,
                            'holding_year' => $holdingYear,
                            'gpa' => $gpa,
                            'status' => 'Passed',
                            'referred_subjects' => null,
                            'raw_text' => $match[0],
                            'center_code' => $centerCode ?? null,
                            'institute_name' => $instName ?? null,
                            'exam_type' => 'regular',
                        ];
                        $lastDetectedDept = ($dept !== "Auto Detect" && $dept !== "General Technology") ? $dept : $lastDetectedDept;
                    }

                    preg_match_all('/\b(\d{6})\s*\{\s*([^}]+)\s*\}/', $blockText, $referredMatches, PREG_SET_ORDER);
                    foreach ($referredMatches as $match) {
                        $roll = $match[1];
                        $contentStr = $match[2];

                        // Marine format: 232726 { GPA_4th-3.75, CGPA-3.39 }
                        if (preg_match_all('/GPA[_-](\d)(?:st|nd|rd|th)[-_]([2-4]\.\d{2})/i', $contentStr, $marineGpaMatches, PREG_SET_ORDER)) {
                            foreach ($marineGpaMatches as $mg) {
                                $semDigit = $mg[1];
                                $gpaVal = (float)$mg[2];
                                $suffix = match ((int)$semDigit) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };
                                $semLabel = $semDigit . $suffix;
                                $results[] = [
                                    'roll' => $roll,
                                    'department' => $dept,
                                    'semester' => $semLabel,
                                    'regulation' => $regulation,
                                    'holding_year' => $holdingYear,
                                    'gpa' => $gpaVal,
                                    'status' => 'Passed',
                                    'referred_subjects' => null,
                                    'raw_text' => $match[0],
                                    'center_code' => $centerCode ?? null,
                                    'institute_name' => $instName ?? null,
                                    'exam_type' => 'regular',
                                ];
                            }
                            $lastDetectedDept = ($dept !== "Auto Detect" && $dept !== "General Technology") ? $dept : $lastDetectedDept;
                            continue;
                        }

                        $hasMultiGpa = preg_match_all('/gpa(\d)\s*:\s*(ref|[2-4]\.\d{2})/i', $contentStr, $multiGpaMatches, PREG_SET_ORDER);

                        if ($hasMultiGpa && count($multiGpaMatches) > 1) {
                            preg_match_all('/ref_sub\s*:\s*([^\}]+)/i', $contentStr, $refSubMatch);
                            $refSubjectsRaw = $refSubMatch[1][0] ?? '';
                            preg_match_all('/\b\d{5,6}(?:\([^)]+\))?\b/', $refSubjectsRaw, $refCodeMatches);
                            $referredSubjects = array_values(array_filter(array_map('trim', $refCodeMatches[0] ?? [])));

                            $inferredDept = $this->detectDeptFromSubjects($referredSubjects, '');
                            $studentDept = $inferredDept !== '' ? $inferredDept : $dept;

                            foreach ($multiGpaMatches as $gpaMatch) {
                                $semDigit = $gpaMatch[1];
                                $semValue = $gpaMatch[2];
                                $suffix = match ((int)$semDigit) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };
                                $semLabel = $semDigit . $suffix;

                                if (strtolower($semValue) === 'ref') {
                                    $results[] = [
                                        'roll' => $roll,
                                        'department' => $studentDept,
                                        'semester' => $semLabel,
                                        'regulation' => $regulation,
                                        'holding_year' => $holdingYear,
                                        'gpa' => null,
                                        'status' => 'Referred',
                                        'referred_subjects' => $referredSubjects,
                                        'raw_text' => "gpa{$semDigit}: ref, ref_sub: " . implode(', ', $referredSubjects),
                                        'center_code' => $centerCode ?? null,
                                        'institute_name' => $instName ?? null,
                                        'exam_type' => 'regular',
                                    ];
                                } else {
                                    $results[] = [
                                        'roll' => $roll,
                                        'department' => $studentDept,
                                        'semester' => $semLabel,
                                        'regulation' => $regulation,
                                        'holding_year' => $holdingYear,
                                        'gpa' => (float)$semValue,
                                        'status' => 'Passed',
                                        'referred_subjects' => null,
                                        'raw_text' => "gpa{$semDigit}: {$semValue}",
                                        'center_code' => $centerCode ?? null,
                                        'institute_name' => $instName ?? null,
                                        'exam_type' => 'regular',
                                    ];
                                }
                            }
                        } else {
                            preg_match_all('/\b\d{5,6}(?:\([^)]+\))?\b/', $contentStr, $codeMatches);
                            $referredSubjects = array_filter(array_map('trim', $codeMatches[0] ?? []));
                            $gpa = null;
                            if (preg_match('/([2-4]\.\d{2})/', $contentStr, $gpaMatches)) {
                                $gpa = (float)$gpaMatches[1];
                            }
                            $results[] = [
                                'roll' => $roll,
                                'department' => $dept,
                                'semester' => $chunkSemester,
                                'regulation' => $regulation,
                                'holding_year' => $holdingYear,
                                'gpa' => $gpa,
                                'status' => 'Referred',
                                'referred_subjects' => array_values($referredSubjects),
                                'raw_text' => $match[0],
                                'center_code' => $centerCode ?? null,
                                'institute_name' => $instName ?? null,
                                'exam_type' => 'regular',
                            ];
                        }
                        $lastDetectedDept = ($dept !== "Auto Detect" && $dept !== "General Technology") ? $dept : $lastDetectedDept;
                    }
                }
            }
            }
        }

        DB::beginTransaction();
        try {
            foreach ($results as $result) {
                BtebResult::updateOrCreate(
                    [
                        'roll' => $result['roll'],
                        'semester' => $result['semester'],
                        'regulation' => $result['regulation'],
                    ],
                    [
                        'center_code' => $result['center_code'] ?? null,
                        'institute_name' => $result['institute_name'] ?? null,
                        'department' => $result['department'],
                        'holding_year' => $result['holding_year'],
                        'gpa' => $result['gpa'],
                        'status' => $result['status'],
                        'referred_subjects' => $result['referred_subjects'],
                        'raw_text' => $result['raw_text'],
                        'exam_type' => $result['exam_type'] ?? 'regular',
                    ]
                );
            }
            DB::commit();

            $semesters = array_unique(array_column($results, 'semester'));
            return response()->json([
                'message' => 'PDF imported successfully',
                'semesters_found' => array_values($semesters),
                'total_results' => count($results),
                'passed' => count(array_filter($results, fn($r) => $r['status'] === 'Passed')),
                'referred' => count(array_filter($results, fn($r) => $r['status'] === 'Referred')),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Database error', 'error' => $e->getMessage()], 500);
        }
    }

    private function splitBySemesterHeader(string $text, string $defaultSemester): array
    {
        $headerPattern = '/(?:^|\n)\s*(?:(\d)(?:st|nd|rd|th)\s*(?:Semester|Sem\.?)|(?:Semester|Sem\.?)\s*(\d)|(?:SEM)\s*[-–—]\s*(I{1,3}V?|IX|V?I{0,3}))\b/i';

        $matches = [];
        preg_match_all($headerPattern, $text, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        if (empty($matches)) {
            return [['semester' => $defaultSemester, 'text' => $text]];
        }

        $chunks = [];
        for ($i = 0; $i < count($matches); $i++) {
            $semNumber = $matches[$i][1] ?? $matches[$i][2] ?? null;
            $semLabel = $matches[$i][3] ?? null;

            if ($semNumber === null && $semLabel !== null) {
                $romanMap = ['I' => '1', 'II' => '2', 'III' => '3', 'IV' => '4', 'V' => '5', 'VI' => '6', 'VII' => '7', 'VIII' => '8'];
                $semNumber = $romanMap[strtoupper($semLabel)] ?? null;
            }
            if ($semNumber === null) continue;

            $num = (int)$semNumber;
            $suffix = match ($num) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };
            $detectedSem = $num . $suffix;

            $startOffset = $matches[$i][0][1];
            $endOffset = ($i + 1 < count($matches)) ? $matches[$i + 1][0][1] : strlen($text);

            $chunks[] = [
                'semester' => $detectedSem,
                'text' => substr($text, $startOffset, $endOffset - $startOffset)
            ];
        }

        if (empty($chunks)) {
            return [['semester' => $defaultSemester, 'text' => $text]];
        }

        $firstHeaderOffset = $matches[0][0][1];
        if ($firstHeaderOffset > 0) {
            array_unshift($chunks, [
                'semester' => $defaultSemester,
                'text' => substr($text, 0, $firstHeaderOffset)
            ]);
        }

        return $chunks;
    }

    private function detectDeptFromSubjects(array $subjects, string $defaultDept): string
    {
        $dict = [
            '26411' => 'Civil Technology', '26421' => 'Civil Technology',
            '26431' => 'Civil Technology', '26432' => 'Civil Technology',
            '26433' => 'Civil Technology', '26441' => 'Civil Technology',
            '26442' => 'Civil Technology', '26443' => 'Civil Technology',
            '26444' => 'Civil Technology', '26445' => 'Civil Technology',
            '26446' => 'Civil Technology', '26451' => 'Civil Technology',
            '26452' => 'Civil Technology', '26453' => 'Civil Technology',
            '26454' => 'Civil Technology', '26455' => 'Civil Technology',
            '26456' => 'Civil Technology', '26461' => 'Civil Technology',
            '26462' => 'Civil Technology', '26463' => 'Civil Technology',
            '26464' => 'Civil Technology', '26471' => 'Civil Technology',
            '26472' => 'Civil Technology', '26473' => 'Civil Technology',
            '26474' => 'Civil Technology', '26481' => 'Civil Technology',
            '26521' => 'Civil Technology', '28863' => 'Civil Technology',
            '66421' => 'Civil Technology', '66431' => 'Civil Technology',
            '66432' => 'Civil Technology', '66433' => 'Civil Technology',
            '66434' => 'Civil Technology', '66441' => 'Civil Technology',
            '66442' => 'Civil Technology', '66443' => 'Civil Technology',
            '66444' => 'Civil Technology', '66445' => 'Civil Technology',
            '66451' => 'Civil Technology', '66452' => 'Civil Technology',
            '66453' => 'Civil Technology', '66454' => 'Civil Technology',
            '66455' => 'Civil Technology', '66456' => 'Civil Technology',
            '66461' => 'Civil Technology', '66462' => 'Civil Technology',
            '66463' => 'Civil Technology', '66464' => 'Civil Technology',
            '66465' => 'Civil Technology', '66466' => 'Civil Technology',
            '66471' => 'Civil Technology', '66472' => 'Civil Technology',
            '66473' => 'Civil Technology', '66474' => 'Civil Technology',
            '66475' => 'Civil Technology', '66481' => 'Civil Technology',
            '68873' => 'Civil Technology',
            '28511' => 'Computer Science & Technology',
            '28521' => 'Computer Science & Technology',
            '28522' => 'Computer Science & Technology',
            '28531' => 'Computer Science & Technology',
            '28532' => 'Computer Science & Technology',
            '28541' => 'Computer Science & Technology',
            '28542' => 'Computer Science & Technology',
            '28543' => 'Computer Science & Technology',
            '28544' => 'Computer Science & Technology',
            '28551' => 'Computer Science & Technology',
            '28552' => 'Computer Science & Technology',
            '28553' => 'Computer Science & Technology',
            '28554' => 'Computer Science & Technology',
            '28555' => 'Computer Science & Technology',
            '28556' => 'Computer Science & Technology',
            '28561' => 'Computer Science & Technology',
            '28562' => 'Computer Science & Technology',
            '28563' => 'Computer Science & Technology',
            '28564' => 'Computer Science & Technology',
            '28565' => 'Computer Science & Technology',
            '28566' => 'Computer Science & Technology',
            '28581' => 'Computer Science & Technology',
            '66611' => 'Computer Science & Technology',
            '66612' => 'Computer Science & Technology',
            '66621' => 'Computer Science & Technology',
            '66622' => 'Computer Science & Technology',
            '66623' => 'Computer Science & Technology',
            '66631' => 'Computer Science & Technology',
            '66632' => 'Computer Science & Technology',
            '66633' => 'Computer Science & Technology',
            '66634' => 'Computer Science & Technology',
            '66641' => 'Computer Science & Technology',
            '66642' => 'Computer Science & Technology',
            '66643' => 'Computer Science & Technology',
            '66644' => 'Computer Science & Technology',
            '66645' => 'Computer Science & Technology',
            '66651' => 'Computer Science & Technology',
            '66652' => 'Computer Science & Technology',
            '66653' => 'Computer Science & Technology',
            '66654' => 'Computer Science & Technology',
            '66655' => 'Computer Science & Technology',
            '68546' => 'Computer Science & Technology',
            '66661' => 'Computer Science & Technology',
            '66662' => 'Computer Science & Technology',
            '66663' => 'Computer Science & Technology',
            '66664' => 'Computer Science & Technology',
            '66665' => 'Computer Science & Technology',
            '66666' => 'Computer Science & Technology',
            '66667' => 'Computer Science & Technology',
            '66668' => 'Computer Science & Technology',
            '66671' => 'Computer Science & Technology',
            '66672' => 'Computer Science & Technology',
            '66673' => 'Computer Science & Technology',
            '66674' => 'Computer Science & Technology',
            '66675' => 'Computer Science & Technology',
            '66677' => 'Computer Science & Technology',
            '66681' => 'Computer Science & Technology',
            '26711' => 'Electrical Technology', '26712' => 'Electrical Technology',
            '26721' => 'Electrical Technology', '26722' => 'Electrical Technology',
            '26731' => 'Electrical Technology', '26732' => 'Electrical Technology',
            '26741' => 'Electrical Technology', '26742' => 'Electrical Technology',
            '26743' => 'Electrical Technology', '26751' => 'Electrical Technology',
            '26752' => 'Electrical Technology', '26753' => 'Electrical Technology',
            '26754' => 'Electrical Technology', '26761' => 'Electrical Technology',
            '26763' => 'Electrical Technology', '26811' => 'Electrical Technology',
            '26833' => 'Electrical Technology', '26842' => 'Electrical Technology',
            '26845' => 'Electrical Technology', '26853' => 'Electrical Technology',
            '66711' => 'Electrical Technology', '66712' => 'Electrical Technology',
            '66713' => 'Electrical Technology', '66721' => 'Electrical Technology',
            '66722' => 'Electrical Technology', '66731' => 'Electrical Technology',
            '66732' => 'Electrical Technology', '66733' => 'Electrical Technology',
            '66741' => 'Electrical Technology', '66742' => 'Electrical Technology',
            '66751' => 'Electrical Technology', '66752' => 'Electrical Technology',
            '66753' => 'Electrical Technology', '66761' => 'Electrical Technology',
            '66762' => 'Electrical Technology', '66763' => 'Electrical Technology',
            '66771' => 'Electrical Technology', '66772' => 'Electrical Technology',
            '66773' => 'Electrical Technology', '66774' => 'Electrical Technology',
            '66775' => 'Electrical Technology', '66781' => 'Electrical Technology',
            '66811' => 'Electrical Technology', '66845' => 'Electrical Technology',
            '66823' => 'Electrical Technology', '66842' => 'Electrical Technology',
            '66856' => 'Electrical Technology', '66863' => 'Electrical Technology',
            '66867' => 'Electrical Technology', '66868' => 'Electrical Technology',
            '66841' => 'Electronics Technology', '66843' => 'Electronics Technology',
            '66851' => 'Electronics Technology', '66852' => 'Electronics Technology',
            '66853' => 'Electronics Technology', '66854' => 'Electronics Technology',
            '66855' => 'Electronics Technology', '66861' => 'Electronics Technology',
            '66862' => 'Electronics Technology', '66864' => 'Electronics Technology',
            '66865' => 'Electronics Technology', '66871' => 'Electronics Technology',
            '66872' => 'Electronics Technology', '66873' => 'Electronics Technology',
            '66874' => 'Electronics Technology', '66881' => 'Electronics Technology',
            '68643' => 'Electronics Technology', '68661' => 'Electronics Technology',
            '67041' => 'Telecommunications Technology',
            '67051' => 'Telecommunications Technology',
            '67061' => 'Telecommunications Technology',
            '67062' => 'Telecommunications Technology',
            '67064' => 'Telecommunications Technology',
            '67071' => 'Telecommunications Technology',
            '67072' => 'Telecommunications Technology',
            '67073' => 'Telecommunications Technology',
            '67141' => 'Telecommunications Technology',
            '67151' => 'Telecommunications Technology',
            '67171' => 'Telecommunications Technology',
        ];

        $counts = [];
        foreach ($subjects as $subj) {
            $code = trim(preg_replace('/\([^)]+\)/', '', $subj) ?? '');
            $dept = $dict[$code] ?? null;
            if ($dept !== null) {
                $counts[$dept] = ($counts[$dept] ?? 0) + 1;
            }
        }

        if (empty($counts)) {
            return $defaultDept;
        }

        arsort($counts);
        return (string) array_key_first($counts);
    }
}