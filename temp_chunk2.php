<?php
class Test2 {
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
                // No center codes found — try parsing as-is
                $sections = [['text' => $pageText, 'center_code' => null, 'institute_name' => null]];
            } else {
                $sections = [];
                for ($si = 0; $si < count($codeMatches); $si++) {
                    $centerCode = $codeMatches[$si][1];
                    $instName = trim($codeMatches[$si][2] ?? '');
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
                                'exam_type' => 'regular',
                            ];
                        }
                        $lastDetectedDept = ($dept !== "Auto Detect" && $dept !== "General Technology") ? $dept : $lastDetectedDept;
                    }

                    preg_match_all('/\b(\d{6})\s*\(\s*([^)]+)\s*\)/', $blockText, $passedMatches, PREG_SET_ORDER);
                    foreach ($passedMatches as $match) {
                        $roll = $match[1];
                        $contentStr = $match[2];

                        // Multi-GPA combined format in parentheses:
                        // 885565 (gpa4: 3.13, gpa3: 3.33, gpa2: 3.05, gpa1: 3.43)
                        $parenMultiGpa = preg_match_all('/gpa(\d)\s*:\s*([2-4]\.\d{2})/i', $contentStr, $parenSemMatches, PREG_SET_ORDER);

                        if ($parenMultiGpa > 1) {
                            foreach ($parenSemMatches as $pg) {
                                $semDigit = $pg[1];
                                $gpaVal = (float)$pg[2];
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
                                    'exam_type' => 'regular',
                                ];
                            }
                        } else {
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
                                'exam_type' => 'regular',
                            ];
                        }
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
                            $allReferredSubjects = array_values(array_filter(array_map('trim', $refCodeMatches[0] ?? [])));

                            $inferredDept = $this->detectDeptFromSubjects($allReferredSubjects, '');
                            $studentDept = $inferredDept !== '' ? $inferredDept : $dept;

                            $semesterMap = \App\Utils\BtebSubjectSemesterMap::splitBySemester($allReferredSubjects, $studentDept);

                            foreach ($multiGpaMatches as $gpaMatch) {
                                $semDigit = $gpaMatch[1];
                                $semValue = $gpaMatch[2];
                                $suffix = match ((int)$semDigit) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };
                                $semLabel = $semDigit . $suffix;

                                if (strtolower($semValue) === 'ref') {
                                    $semSubjects = $semesterMap[$semLabel] ?? [];
                                    $results[] = [
                                        'roll' => $roll,
                                        'department' => $studentDept,
                                        'semester' => $semLabel,
                                        'regulation' => $regulation,
                                        'holding_year' => $holdingYear,
                                        'gpa' => null,
                                        'status' => 'Referred',
                                        'referred_subjects' => !empty($semSubjects) ? $semSubjects : $allReferredSubjects,
                                        'raw_text' => "gpa{$semDigit}: ref, ref_sub: " . implode(', ', !empty($semSubjects) ? $semSubjects : $allReferredSubjects),
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
                                        'exam_type' => 'regular',
                                    ];
                                }
                            }
                        } else {
                            preg_match_all('/\b\d{5,6}(?:\([^)]+\))?\b/', $contentStr, $codeMatches);
                            $allCodes = array_filter(array_map('trim', $codeMatches[0] ?? []));
                            $gpa = null;
                            if ($chunkSemDigit !== null && preg_match('/gpa' . $chunkSemDigit . '\s*:\s*([2-4]\.\d{2})/i', $contentStr, $gpaMatches)) {
                                $gpa = (float)$gpaMatches[1];
                            } elseif (preg_match('/([2-4]\.\d{2})/', $contentStr, $gpaMatches)) {
                                $gpa = (float)$gpaMatches[1];
                            }

                            $inferredDept = $this->detectDeptFromSubjects(array_values($allCodes), '');
                            $studentDept = $inferredDept !== '' ? $inferredDept : $dept;

                            $referredSubjects = array_values(array_filter($allCodes, function ($code) {
                                return preg_match('/^\d{5,6}$/', $code);
                            }));

                            if ($gpa !== null) {
                                $results[] = [
                                    'roll' => $roll,
                                    'department' => $studentDept,
                                    'semester' => $chunkSemester,
                                    'regulation' => $regulation,
                                    'holding_year' => $holdingYear,
                                    'gpa' => $gpa,
                                    'status' => 'Passed',
                                    'referred_subjects' => null,
                                    'raw_text' => $match[0],
                                    'exam_type' => 'regular',
                                ];
                            } else {
                                $results[] = [
                                    'roll' => $roll,
                                    'department' => $studentDept,
                                    'semester' => $chunkSemester,
                                    'regulation' => $regulation,
                                    'holding_year' => $holdingYear,
                                    'gpa' => null,
                                    'status' => 'Referred',
                                    'referred_subjects' => $referredSubjects,
                                    'raw_text' => $match[0],
                                    'exam_type' => 'regular',
                                ];
                            }
                        }
                        $lastDetectedDept = ($dept !== "Auto Detect" && $dept !== "General Technology") ? $dept : $lastDetectedDept;
                    }
                }
            }
        }

        DB::beginTransaction();
        try {
            foreach ($results as $result) {
                BtebResult::updateOrCreate(
                    ['roll' => $result['roll'], 'semester' => $result['semester'], 'regulation' => $result['regulation']],
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

}