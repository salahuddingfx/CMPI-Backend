<?php

namespace App\Console\Commands;

use App\Models\ImportJob;
use App\Models\BtebResult;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Parser;

class FastImportBtebResults extends Command
{
    protected $signature = 'bteb:fast-import {driveUrl?} {--holding-year=2025}';
    protected $description = 'Fast parallel import of BTEB results from Google Drive';

    private array $errors = [];
    private array $processedIds = [];

    public function handle(): int
    {
        $driveUrl = $this->argument('driveUrl') ?: 'https://drive.google.com/drive/folders/1ua9pI1wcno_amg7UL3Ox0rgMCrIBcI82';
        $holdingYear = $this->option('holding-year');

        // Create temp dir for downloads
        $tmpDir = storage_path('app/bteb_pdfs');
        if (!is_dir($tmpDir)) mkdir($tmpDir, 0755, true);

        $this->info("Phase 1: Extracting file IDs from Google Drive...");
        $fileIds = $this->extractAllFileIds($driveUrl);
        $this->info("Found " . count($fileIds) . " files");

        if (empty($fileIds)) {
            $this->error("No files found!");
            return Command::FAILURE;
        }

        // Check for already-downloaded files
        $existingFiles = glob($tmpDir . '/*.pdf');
        $existingNames = array_map(fn($f) => basename($f), $existingFiles);
        $this->info("Already downloaded: " . count($existingFiles) . " files");

        $this->info("Phase 2: Downloading PDFs in parallel...");
        $downloaded = $this->downloadParallel($fileIds, $tmpDir);
        $this->info("Downloaded: " . count($downloaded) . " new files");

        $allPdfFiles = glob($tmpDir . '/*.pdf');
        $this->info("Total PDFs ready: " . count($allPdfFiles));

        $this->info("Phase 3: Parsing and importing...");
        $importJob = ImportJob::create([
            'drive_url' => $driveUrl,
            'holding_year' => $holdingYear,
            'status' => 'processing',
            'total_files' => count($allPdfFiles),
        ]);

        $totalResults = 0;
        $pdfParser = new Parser();
        $lastDetectedDept = "Computer Science & Technology";
        $processed = 0;

        foreach ($allPdfFiles as $pdfPath) {
            $fileName = basename($pdfPath);
            $processed++;

            try {
                $isRescrutiny = stripos($fileName, 'rescrutin') !== false || stripos($fileName, 'correction') !== false;
                $meta = $this->parseFilenameForMetadata($fileName);
                $semester = $meta['semester'] ?? '1st';
                $regulation = $meta['regulation'] ?? '2022';

                $pdf = $pdfParser->parseFile($pdfPath);
                $fileResults = [];

                foreach ($pdf->getPages() as $page) {
                    $pageText = $page->getText();
                    $pageResults = $this->parsePdfText($pageText, $semester, $regulation, $holdingYear, $lastDetectedDept, $isRescrutiny);
                    $fileResults = array_merge($fileResults, $pageResults);
                }

                if (!empty($fileResults)) {
                    $savedCount = 0;
                    // Batch insert for speed
                    $chunks = array_chunk($fileResults, 500);
                    foreach ($chunks as $chunk) {
                        DB::beginTransaction();
                        try {
                            foreach ($chunk as $result) {
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
                                $savedCount++;
                            }
                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            $this->errors[] = "DB error in {$fileName}: " . $e->getMessage();
                        }
                    }
                    $totalResults += $savedCount;
                }

                unset($pdf);

                $importJob->update([
                    'processed_files' => $processed,
                    'total_results' => $totalResults,
                ]);

                $this->info("  [{$processed}/" . count($allPdfFiles) . "] {$fileName}: " . count($fileResults) . " results");

            } catch (\Throwable $e) {
                $this->errors[] = "Error processing {$fileName}: " . $e->getMessage();
                $this->warn("  [{$processed}/" . count($allPdfFiles) . "] FAILED: {$fileName}: " . $e->getMessage());
            }
        }

        $importJob->update([
            'status' => 'completed',
            'total_results' => $totalResults,
            'error_log' => !empty($this->errors) ? ['errors' => array_slice($this->errors, -100)] : null,
        ]);

        // Cleanup temp files
        array_map('unlink', $allPdfFiles);
        rmdir($tmpDir);

        $this->newLine();
        $this->info("=== IMPORT COMPLETE ===");
        $this->info("Files processed: {$processed}");
        $this->info("Total results: {$totalResults}");
        $this->info("BTEB results in DB: " . BtebResult::count());
        if (!empty($this->errors)) {
            $this->warn("Errors: " . count($this->errors));
        }

        return Command::SUCCESS;
    }

    private function extractAllFileIds(string $folderUrl): array
    {
        $allIds = [];
        $visited = [];
        $this->extractRecursive($folderUrl, $allIds, $visited);
        return array_values(array_unique($allIds));
    }

    private function extractRecursive(string $folderUrl, array &$allIds, array &$visited): void
    {
        preg_match('/folders\/([a-zA-Z0-9_-]+)/', $folderUrl, $m);
        $folderId = $m[1] ?? null;
        if (!$folderId || in_array($folderId, $visited)) return;
        $visited[] = $folderId;

        $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';
        $html = Http::timeout(30)->withHeaders(['User-Agent' => $ua])->get($folderUrl)->body();

        if (!$html) return;

        // Parse _DRIVE_ivd
        if (preg_match('/window\[\'_DRIVE_ivd\'\]\s*=\s*\'(.+?)\'\s*;/s', $html, $match)) {
            $decoded = preg_replace_callback('/\\\\x([0-9a-fA-F]{2})/', fn($m) => chr(hexdec($m[1])), $match[1]);
            $json = json_decode($decoded, true);
            $items = $json[0] ?? $json;

            if (is_array($items)) {
                foreach ($items as $item) {
                    if (!is_array($item) || count($item) < 4) continue;
                    $id = $item[0] ?? null;
                    $name = $item[2] ?? '';
                    $mimeType = $item[3] ?? '';
                    if (!is_string($id) || strlen($id) < 20) continue;

                    if (strpos($mimeType, 'folder') !== false && $id !== $folderId) {
                        $this->extractRecursive("https://drive.google.com/drive/folders/{$id}", $allIds, $visited);
                    } elseif (strpos($mimeType, 'folder') === false) {
                        $allIds[] = $id;
                    }
                }
            }
        }

        // Also get data-id attributes
        preg_match_all('/data-id="([a-zA-Z0-9_-]{25,50})"/', $html, $dataIdMatches);
        foreach ($dataIdMatches[1] as $id) {
            if (!in_array($id, $visited)) {
                $allIds[] = $id;
            }
        }
    }

    private function downloadParallel(array $fileIds, string $tmpDir): array
    {
        $downloaded = [];
        $batchSize = 10;

        $chunks = array_chunk($fileIds, $batchSize);
        foreach ($chunks as $chunkIndex => $chunk) {
            $multi = curl_multi_init();
            $handles = [];

            foreach ($chunk as $index => $fileId) {
                $tmpFile = $tmpDir . '/' . $fileId . '.pdf';
                if (file_exists($tmpFile)) {
                    $downloaded[] = $fileId;
                    continue;
                }

                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => "https://drive.google.com/uc?export=download&id={$fileId}&confirm=no_antivirus",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_TIMEOUT => 120,
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ]);
                $handles[$fileId] = ['curl' => $ch, 'tmpFile' => $tmpFile];
                curl_multi_add_handle($multi, $ch);
            }

            // Execute all downloads in parallel
            do {
                $status = curl_multi_exec($multi, $active);
                if ($active) curl_multi_select($multi, 1);
            } while ($active && $status === CURLM_OK);

            foreach ($handles as $fileId => $info) {
                $body = curl_multi_getcontent($info['curl']);
                $httpCode = curl_getinfo($info['curl'], CURLINFO_HTTP_CODE);

                if ($body && strpos($body, '%PDF') === 0) {
                    file_put_contents($info['tmpFile'], $body);
                    $downloaded[] = $fileId;
                }

                curl_multi_remove_handle($multi, $info['curl']);
                curl_close($info['curl']);
            }

            curl_multi_close($multi);

            $total = count($downloaded);
            $this->info("  Batch " . ($chunkIndex + 1) . "/" . count($chunks) . " done ({$total} files downloaded)");
        }

        return $downloaded;
    }

    private function parseFilenameForMetadata(string $fileName): array
    {
        $result = ['semester' => null, 'regulation' => null];

        if (preg_match('/(\d)(?:st|nd|rd|th)/i', $fileName, $m)) {
            $result['semester'] = $m[1] . ['st','nd','rd','th'][$m[1]-1] ?? 'th';
        }

        if (preg_match('/20(16|22|10)/i', $fileName, $m)) {
            $result['regulation'] = '20' . $m[1];
        }

        return $result;
    }

    private function parsePdfText(string $text, string $semester, string $regulation, string $holdingYear, string &$lastDetectedDept, bool $isRescrutiny): array
    {
        $results = [];
        $sections = preg_split('/\b(\d{5})\s*[-–]\s*/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        for ($i = 1; $i < count($sections); $i += 2) {
            $centerCode = $sections[$i];
            $sectionText = $sections[$i + 1] ?? '';

            if (!preg_match('/[A-Za-z]/', $sectionText)) continue;

            $instituteName = '';
            if (preg_match('/^([^\n]+)/', trim($sectionText), $nameMatch)) {
                $instituteName = trim($nameMatch[1]);
            }

            $semesterChunks = $this->splitBySemesterHeader($sectionText);

            foreach ($semesterChunks as $semLabel => $semText) {
                $detectedSemester = $this->extractSemesterNumber($semLabel) ?: $semester;

                $deptBlocks = $this->splitByDepartment($semText);
                foreach ($deptBlocks as $dept => $deptText) {
                    $lastDetectedDept = $dept;
                    $deptResults = $this->extractResultsFromText($deptText, $detectedSemester, $regulation, $holdingYear, $centerCode, $instituteName, $dept, $isRescrutiny);
                    $results = array_merge($results, $deptResults);
                }
            }
        }

        return $results;
    }

    private function splitBySemesterHeader(string $text): array
    {
        $chunks = [];
        $pattern = '/(?:^|\n)\s*(?:(\d)(?:st|nd|rd|th)\s*(?:Semester|Sem\.?)|(?:Semester|Sem\.?)\s*(\d)|(?:SEM)\s*[-–—]\s*(I{1,3}V?|IX|V?I{0,3}))\b/i';

        preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);

        if (empty($matches[0])) {
            $chunks['default'] = $text;
            return $chunks;
        }

        for ($i = 0; $i < count($matches[0]); $i++) {
            $label = trim($matches[0][$i][0]);
            $start = $matches[0][$i][1] + strlen($matches[0][$i][0]);
            $end = $i + 1 < count($matches[0]) ? $matches[0][$i+1][1] : strlen($text);
            $chunks[$label] = substr($text, $start, $end - $start);
        }

        return $chunks;
    }

    private function extractSemesterNumber(string $label): ?string
    {
        if (preg_match('/(\d)/', $label, $m)) {
            $n = (int)$m[1];
            $suffixes = ['st','nd','rd','th','th','th','th','th','th','th'];
            return $n . ($suffixes[$n-1] ?? 'th');
        }
        if (preg_match('/I{1,3}V?|IX|V?I{0,3}/i', $label, $m)) {
            $map = ['I'=>'1st','II'=>'2nd','III'=>'3rd','IV'=>'4th','V'=>'5th','VI'=>'6th','VII'=>'7th','VIII'=>'8th','IX'=>'9th'];
            return $map[strtoupper($m[0])] ?? null;
        }
        return null;
    }

    private function splitByDepartment(string $text): array
    {
        $depts = [];
        $pattern = '/(\d{3})\s*[-–—]\s*([A-Za-z\s&\'-]+?)(?:\n|$)/m';
        preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);

        if (empty($matches[0])) {
            $depts[$this->guessDeptFromText($text)] = $text;
            return $depts;
        }

        for ($i = 0; $i < count($matches[0]); $i++) {
            $code = $matches[1][$i][0];
            $name = trim($matches[2][$i][0]);
            $start = $matches[0][$i][1] + strlen($matches[0][$i][0]);
            $end = $i + 1 < count($matches[0]) ? $matches[0][$i+1][1] : strlen($text);
            $dept = $this->mapDeptCode($code) ?: $name;
            $depts[$dept] = substr($text, $start, $end - $start);
        }

        return $depts;
    }

    private function guessDeptFromText(string $text): string
    {
        if (preg_match_all('/\b264\d{2}\b/', $text)) return 'Civil Technology';
        if (preg_match_all('/\b285\d{2}\b/', $text)) return 'Computer Science & Technology';
        if (preg_match_all('/\b267\d{2}\b/', $text)) return 'Electrical Technology';
        if (preg_match_all('/\b268\d{2}\b/', $text)) return 'Electronics Technology';
        if (preg_match_all('/\b279\d{2}\b/', $text)) return 'Marine Technology';
        if (preg_match_all('/\b270\d{2}\b/', $text)) return 'Mechanical Technology';
        return 'Computer Science & Technology';
    }

    private function mapDeptCode(string $code): ?string
    {
        $map = [
            '664'=>'Civil Technology','264'=>'Civil Technology',
            '666'=>'Computer Science & Technology','285'=>'Computer Science & Technology',
            '667'=>'Electrical Technology','267'=>'Electrical Technology',
            '668'=>'Electronics Technology','268'=>'Electronics Technology',
            '670'=>'Telecommunication Technology','671'=>'Telecommunication Technology',
            '279'=>'Marine Technology','270'=>'Mechanical Technology',
            '262'=>'Automobile Technology','263'=>'Chemical Technology',
            '269'=>'Food Technology','271'=>'Power Technology',
            '272'=>'Refrigeration & Air Conditioning Technology',
            '280'=>'Shipbuilding Technology','286'=>'Electromedical Technology',
            '687'=>'Architecture Technology','282'=>'Ceramic Technology',
        ];
        return $map[$code] ?? null;
    }

    private function extractResultsFromText(string $text, string $semester, string $regulation, string $holdingYear, string $centerCode, string $instituteName, string $dept, bool $isRescrutiny): array
    {
        $results = [];
        $examType = $isRescrutiny ? 'rescrutiny' : 'regular';

        // Pattern 1: CGPA format
        if (preg_match_all('/\b(\d{6})\s+cgpa:\s*([2-4]\.\d{2})\s*\(\s*([\s\S]+?)\s*\)/i', $text, $m)) {
            for ($i = 0; $i < count($m[0]); $i++) {
                $roll = $m[1][$i];
                $inner = trim($m[3][$i]);
                if (preg_match_all('/gpa(\d):\s*([2-4]\.\d{2}|ref)/i', $inner, $gm)) {
                    for ($j = 0; $j < count($gm[0]); $j++) {
                        $sem = $this->numToSem((int)$gm[1][$j]);
                        $gpa = $gm[2][$j] === 'ref' ? null : (float)$gm[2][$j];
                        $results[] = $this->makeResult($roll, $sem, $regulation, $holdingYear, $gpa, $gpa ? 'Passed' : 'Referred', $centerCode, $instituteName, $dept, null, $examType);
                    }
                } else {
                    $results[] = $this->makeResult($roll, $semester, $regulation, $holdingYear, (float)$m[2][$i], 'Passed', $centerCode, $instituteName, $dept, null, $examType);
                }
            }
        }

        // Pattern 2: GPA with parens (multi or single)
        if (preg_match_all('/\b(\d{6})\s*\(\s*([^)]+)\s*\)/', $text, $m)) {
            for ($i = 0; $i < count($m[0]); $i++) {
                $roll = $m[1][$i];
                $inner = trim($m[2][$i]);
                if (preg_match_all('/gpa(\d):\s*([2-4]\.\d{2}|ref)/i', $inner, $gm)) {
                    for ($j = 0; $j < count($gm[0]); $j++) {
                        $sem = $this->numToSem((int)$gm[1][$j]);
                        $gpa = $gm[2][$j] === 'ref' ? null : (float)$gm[2][$j];
                        $results[] = $this->makeResult($roll, $sem, $regulation, $holdingYear, $gpa, $gpa ? 'Passed' : 'Referred', $centerCode, $instituteName, $dept, null, $examType);
                    }
                } elseif (preg_match('/^[2-4]\.\d{2}$/', $inner)) {
                    $results[] = $this->makeResult($roll, $semester, $regulation, $holdingYear, (float)$inner, 'Passed', $centerCode, $instituteName, $dept, null, $examType);
                }
            }
        }

        // Pattern 3: Referred with curly braces
        if (preg_match_all('/\b(\d{6})\s*\{\s*([^}]+)\s*\}/', $text, $m)) {
            for ($i = 0; $i < count($m[0]); $i++) {
                $roll = $m[1][$i];
                $inner = trim($m[2][$i]);

                if (preg_match_all('/GPA_(\w+)-(\d\.\d{2})/i', $inner, $gm)) {
                    for ($j = 0; $j < count($gm[0]); $j++) {
                        $sem = $this->labelToSem($gm[1][$j]);
                        $results[] = $this->makeResult($roll, $sem, $regulation, $holdingYear, (float)$gm[2][$j], 'Passed', $centerCode, $instituteName, $dept, null, $examType);
                    }
                } elseif (preg_match_all('/gpa(\d):\s*(ref|[2-4]\.\d{2})/i', $inner, $gm)) {
                    $refs = [];
                    if (preg_match('/ref_sub:\s*([0-9,\s(T)]+)/i', $inner, $refM)) {
                        $refs = array_map('trim', explode(',', $refM[1]));
                    }
                    for ($j = 0; $j < count($gm[0]); $j++) {
                        $sem = $this->numToSem((int)$gm[1][$j]);
                        if ($gm[2][$j] === 'ref') {
                            $results[] = $this->makeResult($roll, $sem, $regulation, $holdingYear, null, 'Referred', $centerCode, $instituteName, $dept, $refs, $examType);
                        } else {
                            $results[] = $this->makeResult($roll, $sem, $regulation, $holdingYear, (float)$gm[2][$j], 'Passed', $centerCode, $instituteName, $dept, null, $examType);
                        }
                    }
                } else {
                    $refs = array_map('trim', explode(',', $inner));
                    $refs = array_filter($refs, fn($r) => preg_match('/^\d{5}/', $r));
                    $results[] = $this->makeResult($roll, $semester, $regulation, $holdingYear, null, 'Referred', $centerCode, $instituteName, $dept, array_values($refs), $examType);
                }
            }
        }

        // Pattern 4: Simple roll gpa (no parens)
        if (preg_match_all('/\b(\d{6})\s+([2-4]\.\d{2})\b/', $text, $m)) {
            foreach ($m[1] as $i => $roll) {
                $results[] = $this->makeResult($roll, $semester, $regulation, $holdingYear, (float)$m[2][$i], 'Passed', $centerCode, $instituteName, $dept, null, $examType);
            }
        }

        return $results;
    }

    private function makeResult(string $roll, string $semester, string $regulation, string $holdingYear, ?float $gpa, string $status, string $centerCode, string $instituteName, string $dept, ?array $refs, string $examType): array
    {
        return [
            'roll' => $roll,
            'semester' => $semester,
            'regulation' => $regulation,
            'holding_year' => $holdingYear,
            'gpa' => $gpa,
            'status' => $status,
            'center_code' => $centerCode,
            'institute_name' => $instituteName,
            'department' => $dept,
            'referred_subjects' => $refs,
            'raw_text' => null,
            'exam_type' => $examType,
        ];
    }

    private function numToSem(int $n): string
    {
        $suffixes = ['st','nd','rd','th','th','th','th','th','th','th'];
        return $n . ($suffixes[$n-1] ?? 'th');
    }

    private function labelToSem(string $label): string
    {
        $map = ['1st'=>'1st','2nd'=>'2nd','3rd'=>'3rd','4th'=>'4th','5th'=>'5th','6th'=>'6th','7th'=>'7th','8th'=>'8th',
                '1'=>'1st','2'=>'2nd','3'=>'3rd','4'=>'4th','5'=>'5th','6'=>'6th','7'=>'7th','8'=>'8th'];
        return $map[strtolower($label)] ?? '1st';
    }
}
