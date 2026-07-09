<?php

namespace App\Jobs;

use App\Models\ImportJob;
use App\Models\BtebResult;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class ProcessBtebDriveImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries = 3;

    public function __construct(
        public ImportJob $importJob,
        public string $driveUrl,
        public ?string $semester = null,
        public ?string $regulation = null,
        public ?string $holdingYear = null
    ) {}

    public function handle(): void
    {
        ini_set('memory_limit', '512M');
        $this->importJob->update(['status' => 'processing']);

        try {
            // Extract files with their folder context
            $extractResult = $this->extractFileIds($this->driveUrl);
            $fileIds = $extractResult['ids'];
            $fileFolders = $extractResult['file_folders'];
            $fileNames = $extractResult['file_names'] ?? [];

            if (empty($fileIds)) {
                $this->importJob->update([
                    'status' => 'failed',
                    'error_log' => ['message' => 'No valid files found in the Google Drive folder.'],
                ]);
                return;
            }

            // Resume support: skip already processed files
            $processedIds = $this->importJob->error_log['processed_file_ids'] ?? [];
            $remainingIds = array_values(array_filter($fileIds, fn($id) => !in_array($id, $processedIds)));
            $totalFiles = count($processedIds) + count($remainingIds);
            $this->importJob->update(['total_files' => $totalFiles]);

            // Detect holding year
            $holdingYear = $this->holdingYear ?? $this->detectHoldingYearFromUrl($this->driveUrl) ?? date('Y');

            // PHASE 1: Parallel download all PDFs to temp directory
            $tmpDir = storage_path('app/bteb_tmp_' . $this->importJob->id);
            if (!is_dir($tmpDir)) mkdir($tmpDir, 0755, true);

            $this->importJob->update(['error_log' => ['phase' => 'downloading', 'processed_file_ids' => $processedIds]]);
            $downloaded = $this->downloadParallel($remainingIds, $tmpDir, $fileFolders, $fileNames);

            // PHASE 2: Process each PDF locally (no network = fast)
            $errors = $this->importJob->error_log['errors'] ?? [];
            $pdfParser = new Parser();
            $processedCount = count($processedIds);
            $totalSaved = $this->importJob->total_results ?? 0;

            foreach ($downloaded as $fileId => $filePath) {
                $fileName = basename($filePath);
                try {
                    $isRescrutiny = $this->isRescrutinyFile($fileName) || $this->isRescrutinyFolder($fileFolders[$fileId] ?? '');

                    $meta = $this->parseFilenameForMetadata($fileName);
                    $semester = $this->semester ?? $meta['semester'] ?? '1st';
                    $regulation = $this->regulation ?? $meta['regulation'] ?? '2022';

                    $pdf = $pdfParser->parseFile($filePath);
                    $lastDetectedDept = "Computer Science & Technology";

                    $fileResults = [];
                    foreach ($pdf->getPages() as $page) {
                        $pageText = $page->getText();
                        $pageResults = $this->parsePdfText($pageText, $semester, $regulation, $holdingYear, $lastDetectedDept, $isRescrutiny);
                        if (!empty($pageResults)) {
                            $fileResults = array_merge($fileResults, $pageResults);
                        }
                    }

                    if (!empty($fileResults)) {
                        $totalSaved += $this->bulkSaveResults($fileResults);
                    }

                    unset($fileResults, $pdf);
                    gc_collect_cycles();
                } catch (\Throwable $e) {
                    $errors[] = "Error processing {$fileName}: " . $e->getMessage();
                }

                $processedCount++;
                $processedIds[] = $fileId;
                $this->importJob->update([
                    'processed_files' => $processedCount,
                    'total_results' => $totalSaved,
                    'error_log' => ['errors' => array_slice($errors, -50), 'processed_file_ids' => $processedIds],
                ]);
            }

            // Cleanup temp dir
            $this->cleanupDir($tmpDir);

            $this->importJob->update([
                'status' => 'completed',
                'total_results' => $totalSaved,
                'error_log' => !empty($errors) ? ['errors' => array_slice($errors, -50)] : null,
            ]);

            \App\Models\Notification::create([
                'title' => 'BTEB Result Processed',
                'description' => "Successfully processed BTEB results import job. " . number_format($totalSaved) . " records imported.",
                'type' => 'success',
            ]);

        } catch (\Throwable $e) {
            Log::error("BTEB Drive Import failed: " . $e->getMessage());
            $this->importJob->update([
                'status' => 'failed',
                'error_log' => ['message' => $e->getMessage()],
            ]);

            \App\Models\Notification::create([
                'title' => 'BTEB Result Import Failed',
                'description' => "Google Drive results import job failed: " . $e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    private function downloadParallel(array $fileIds, string $tmpDir, array $fileFolders, array $fileNames): array
    {
        $downloaded = [];
        $batchSize = 10;
        $chunks = array_chunk($fileIds, $batchSize);

        foreach ($chunks as $chunkIndex => $chunk) {
            $multi = curl_multi_init();
            $handles = [];

            foreach ($chunk as $fileId) {
                $name = $fileNames[$fileId] ?? ($fileId . '.pdf');
                $safeName = str_replace(['/', '\\'], '_', $name);
                if (!str_ends_with(strtolower($safeName), '.pdf')) {
                    $safeName .= '.pdf';
                }
                $tmpFile = $tmpDir . '/' . $safeName;
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => "https://drive.google.com/uc?export=download&id={$fileId}&confirm=no_antivirus",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_TIMEOUT => 120,
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                ]);
                $handles[$fileId] = ['curl' => $ch, 'tmpFile' => $tmpFile];
                curl_multi_add_handle($multi, $ch);
            }

            do {
                $status = curl_multi_exec($multi, $active);
                if ($active) curl_multi_select($multi, 1);
            } while ($active && $status === CURLM_OK);

            foreach ($handles as $fileId => $info) {
                $body = curl_multi_getcontent($info['curl']);
                if ($body && strpos($body, '%PDF') === 0) {
                    file_put_contents($info['tmpFile'], $body);
                    $downloaded[$fileId] = $info['tmpFile'];
                }
                curl_multi_remove_handle($multi, $info['curl']);
                curl_close($info['curl']);
            }
            curl_multi_close($multi);
        }

        return $downloaded;
    }

    private function bulkSaveResults(array $results): int
    {
        $saved = 0;
        $chunks = array_chunk($results, 500);

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
                    $saved++;
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
            }
        }

        return $saved;
    }

    private function cleanupDir(string $dir): void
    {
        if (!is_dir($dir)) return;
        $files = glob($dir . '/*');
        if (is_array($files)) {
            foreach ($files as $file) {
                if (is_file($file)) @unlink($file);
            }
        }
        @rmdir($dir);
    }

    private function downloadDriveFile(string $fileId): ?array
    {
        $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';

        $steps = [
            "https://drive.google.com/uc?export=download&id={$fileId}",
        ];

        $lastBody = '';

        foreach ($steps as $url) {
            $response = Http::withoutVerifying()->timeout(90)->withHeaders(['User-Agent' => $ua])->get($url);
            if (!$response->successful()) continue;

            $body = $response->body();

            // If body starts with %PDF, it's a direct download
            if (strpos($body, '%PDF') === 0) {
                $disposition = $response->header('Content-Disposition');
                $filename = $this->extractFilenameFromDisposition($disposition, $fileId);
                return ['content' => $body, 'filename' => $filename];
            }

            $lastBody = $body;

            // Large file — extract confirm token and retry
            if (preg_match('/name="confirm"\s+value="([^"]+)"/', $body, $tokenMatch)) {
                $steps[] = "https://drive.google.com/uc?export=download&id={$fileId}&confirm=" . $tokenMatch[1];
            }

            // Newer format — extract download link
            if (preg_match('/id="uc-download-link"[^>]*href="([^"]+)"/', $body, $dlMatch)) {
                $steps[] = 'https://drive.google.com' . html_entity_decode($dlMatch[1]);
            }
        }

        // Old fallback
        $fallbackUrl = "https://drive.google.com/uc?export=download&id={$fileId}&confirm=no_antivirus";
        $response = Http::withoutVerifying()->timeout(90)->withHeaders(['User-Agent' => $ua])->get($fallbackUrl);
        if ($response->successful()) {
            $body = $response->body();
            if (strpos($body, '%PDF') === 0) {
                $disposition = $response->header('Content-Disposition');
                $filename = $this->extractFilenameFromDisposition($disposition, $fileId);
                return ['content' => $body, 'filename' => $filename];
            }
        }

        return null;
    }

    private function extractFilenameFromDisposition(?string $disposition, string $fallback): string
    {
        if ($disposition && preg_match('/filename\*?=["\']?(?:UTF-8\'\')?([^"\';\n]+)/i', $disposition, $m)) {
            return urldecode(trim($m[1], ' "\''));
        }
        return $fallback . '.pdf';
    }

    private function extractFileIds(string $folderUrl): array
    {
        $allIds = [];
        $fileFolders = [];
        $fileNames = [];
        $visitedFolders = [];
        $this->extractFileIdsRecursive($folderUrl, $allIds, $fileFolders, $fileNames, $visitedFolders);
        return [
            'ids' => array_values(array_unique($allIds)),
            'file_folders' => $fileFolders,
            'file_names' => $fileNames,
        ];
    }

    private function extractFileIdsRecursive(string $folderUrl, array &$allIds, array &$fileFolders, array &$fileNames, array &$visitedFolders, string $parentFolderName = ''): void
    {
        preg_match('/folders\/([a-zA-Z0-9_-]+)/', $folderUrl, $folderMatch);
        $folderId = $folderMatch[1] ?? null;

        if (!$folderId) {
            preg_match('/d\/([a-zA-Z0-9_-]{25,50})/', $folderUrl, $fileMatch);
            if (isset($fileMatch[1])) {
                $allIds[] = $fileMatch[1];
                $fileFolders[$fileMatch[1]] = $parentFolderName;
                $fileNames[$fileMatch[1]] = $fileMatch[1] . '.pdf';
            }
            return;
        }

        if (in_array($folderId, $visitedFolders)) return;
        $visitedFolders[] = $folderId;

        $html = $this->fetchFolderHtml($folderUrl, $folderId);
        if (!$html) return;

        // Try to extract current folder name from breadcrumb
        $currentFolderName = $parentFolderName;
        if (preg_match('/data-tooltip="([^"]+)"/', $html, $fnameMatch)) {
            $currentFolderName = $fnameMatch[1];
        } elseif (preg_match('/<title>([^<]+)<\/title>/', $html, $titleMatch)) {
            $title = trim($titleMatch[1]);
            $title = preg_replace('/\s*-\s*Google Drive\s*$/i', '', $title);
            if ($title !== '' && !in_array(strtolower($title), ['my drive', 'drive'])) {
                $currentFolderName = $title;
            }
        }

        // Parse _DRIVE_ivd to get file entries with name, type, and parent info
        $ivdData = $this->parseDriveIvd($html);

        // Also extract data-id attributes for completeness
        preg_match_all('/data-id="([a-zA-Z0-9_-]{25,50})"/', $html, $dataIdMatches);
        $dataIds = $dataIdMatches[1] ?? [];

        // Collect all known IDs from both sources
        $allKnownIds = [];
        foreach ($ivdData as $entry) {
            $allKnownIds[$entry['id']] = $entry;
        }
        foreach ($dataIds as $id) {
            if (!isset($allKnownIds[$id])) {
                $allKnownIds[$id] = ['id' => $id, 'name' => '', 'type' => 'unknown', 'parent' => ''];
            }
        }

        $subfolderIds = [];
        foreach ($allKnownIds as $entry) {
            $id = $entry['id'];
            $type = $entry['type'] ?? 'unknown';

            if ($type === 'folder') {
                if ($id !== $folderId && !in_array($id, $visitedFolders)) {
                    // Track subfolder name
                    $subName = $entry['name'] ?? '';
                    $subfolderIds[$id] = $subName;
                }
            } elseif ($type === 'file') {
                $allIds[] = $id;
                $fileFolders[$id] = $currentFolderName;
                $fileNames[$id] = $entry['name'] ?? ($id . '.pdf');
            } else {
                // Unknown type - try to determine by checking if it's a subfolder
                if ($id !== $folderId && !in_array($id, $visitedFolders)) {
                    if (preg_match('/\/folders\/' . preg_quote($id, '/') . '/', $html)) {
                        $subfolderIds[$id] = $entry['name'] ?? '';
                    } else {
                        $allIds[] = $id;
                        $fileFolders[$id] = $currentFolderName;
                        $fileNames[$id] = $entry['name'] ?? ($id . '.pdf');
                    }
                }
            }
        }

        foreach ($subfolderIds as $subFolderId => $subFolderName) {
            $subUrl = "https://drive.google.com/drive/folders/{$subFolderId}";
            $this->extractFileIdsRecursive($subUrl, $allIds, $fileFolders, $fileNames, $visitedFolders, $subFolderName);
        }
    }

    /**
     * Parse the _DRIVE_ivd JavaScript variable to extract file/folder entries.
     * Format: [[id, [parentId], name, mimeType, ...], ...]
     */
    private function parseDriveIvd(string $html): array
    {
        $entries = [];

        // Find the _DRIVE_ivd assignment
        if (!preg_match('/window\[\'_DRIVE_ivd\'\]\s*=\s*\'(.+?)\'\s*;/s', $html, $match)) {
            return $entries;
        }

        // Decode hex escapes (\x5b -> [, \x22 -> ", etc.)
        $raw = $match[1];
        $decoded = preg_replace_callback('/\\\\x([0-9a-fA-F]{2})/', function ($m) {
            return chr(hexdec($m[1]));
        }, $raw);

        // The decoded string is valid JSON - parse it directly
        $json = json_decode($decoded, true);
        if (!is_array($json)) return $entries;

        // Structure: [[[id, [parentId], name, mimeType, ...], ...]]
        // Outermost array wraps another array which holds the entries
        $items = $json[0] ?? $json;
        if (!is_array($items)) return $entries;

        foreach ($items as $item) {
            if (!is_array($item) || count($item) < 4) continue;

            $id = $item[0] ?? null;
            $parentArr = $item[1] ?? null;
            $name = $item[2] ?? '';
            $mimeType = $item[3] ?? '';

            if (!is_string($id) || strlen($id) < 20) continue;

            $parent = is_array($parentArr) ? ($parentArr[0] ?? '') : '';
            $isFolder = strpos($mimeType, 'folder') !== false;

            $entries[] = [
                'id' => $id,
                'parent' => $parent,
                'name' => $name,
                'type' => $isFolder ? 'folder' : 'file',
            ];
        }

        return $entries;
    }

    private function fetchFolderHtml(string $folderUrl, string $folderId): ?string
    {
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.5',
        ];

        // Method 1: Main folder URL
        try {
            $response = Http::withoutVerifying()->withHeaders($headers)->timeout(20)->get($folderUrl);
            if ($response->successful()) {
                $html = $response->body();
                if ($this->htmlHasFileIds($html, $folderId)) {
                    return $html;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Folder fetch method 1 failed for {$folderId}: " . $e->getMessage());
        }

        // Method 2: Embedded view
        try {
            $embedUrl = "https://drive.google.com/embeddedfolderview?id={$folderId}#list";
            $response = Http::withoutVerifying()->withHeaders($headers)->timeout(20)->get($embedUrl);
            if ($response->successful()) {
                $html = $response->body();
                if ($this->htmlHasFileIds($html, $folderId)) {
                    return $html;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Folder fetch method 2 failed for {$folderId}: " . $e->getMessage());
        }

        // Method 3: usp=sharing
        try {
            $altUrl = "https://drive.google.com/drive/folders/{$folderId}?usp=sharing";
            $response = Http::withoutVerifying()->withHeaders($headers)->timeout(20)->get($altUrl);
            if ($response->successful()) {
                return $response->body();
            }
        } catch (\Throwable $e) {
            Log::warning("Folder fetch method 3 failed for {$folderId}: " . $e->getMessage());
        }

        return null;
    }

    private function htmlHasFileIds(string $html, string $folderId): bool
    {
        preg_match_all('/["\']([a-zA-Z0-9_-]{28,44})["\']/', $html, $matches);
        if (empty($matches[1])) return false;

        foreach ($matches[1] as $id) {
            if ($id !== $folderId && strlen($id) >= 28) {
                return true;
            }
        }
        return false;
    }

    private function detectHoldingYearFromUrl(string $url): ?string
    {
        // Try to extract year from folder name patterns like "2025 (Held in Jan-March, 26)"
        preg_match('/(\d{4})\s*\(Held/i', $url, $matches);
        if (!empty($matches[1])) {
            return $matches[1];
        }

        // Try just a 4-digit year in the URL
        preg_match('/(\d{4})/', $url, $matches);
        if (!empty($matches[1])) {
            $year = (int)$matches[1];
            if ($year >= 2020 && $year <= 2030) {
                return (string)$year;
            }
        }

        return null;
    }

    /**
     * Parse filename to extract semester and regulation.
     * Examples:
     *   RESULT_4th_2022_Regulation.pdf â†’ semester=4th, regulation=2022
     *   RESULT_8th_Irr_2016_Regulation.pdf â†’ semester=8th, regulation=2016
     *   RESULT_6th_Tourism.pdf â†’ semester=6th, regulation=null
     *   RESULT_ALLIED_1ST.pdf â†’ semester=1st, regulation=null
     *   MARINE_TRADE_RESULT_2ND_2025_1.pdf â†’ semester=2nd, regulation=null
     */
    private function parseFilenameForMetadata(string $fileName): array
    {
        $semester = null;
        $regulation = null;

        // Clean filename
        $base = pathinfo($fileName, PATHINFO_FILENAME);

        // Extract semester (1st, 2nd, 3rd, 4th, 5th, 6th, 7th, 8th)
        if (preg_match('/(\d)(?:st|nd|rd|th)\b/i', $base, $semMatch)) {
            $num = $semMatch[1];
            $suffix = $semMatch[0];
            $semester = $num . strtolower($suffix);
        }
        // Also check ALLIED_1ST, ALLIED_2ND patterns
        if (preg_match('/ALLIED[_\s]+(\d)(st|nd|rd|th)/i', $base, $alliedMatch)) {
            $semester = strtolower($alliedMatch[1] . $alliedMatch[2]);
        }

        // Extract regulation (4-digit year like 2022, 2016, 2010)
        if (preg_match('/20(10|16|22)/', $base, $regMatch)) {
            $regulation = $regMatch[0];
        }

        return [
            'semester' => $semester,
            'regulation' => $regulation,
        ];
    }

    private function isRescrutinyFolder(string $folderName): bool
    {
        $lower = strtolower($folderName);
        $keywords = ['rescrutiny', 'scrutiny', 'correction', 'challenge', 'recheck', 're-check', 're Scrutiny'];
        foreach ($keywords as $kw) {
            if (str_contains($lower, $kw)) return true;
        }
        return false;
    }

    private function isRescrutinyFile(string $fileName): bool
    {
        $lower = strtolower($fileName);
        $keywords = ['rescrutiny', 'scrutiny', 'correction', 'challenge', 'recheck', 're-check'];
        foreach ($keywords as $kw) {
            if (str_contains($lower, $kw)) return true;
        }
        return false;
    }

    private function parsePdfText(string $pageText, string $semester, string $regulation, string $holdingYear, &$lastDetectedDept, bool $isRescrutiny = false): array
    {
        $results = [];

        // Split page into institute sections by center code boundaries
        // Format: "74026 - Cox's Bazar Model Polytechnic Institute"
        // or just "74026" followed by text
        $sections = $this->splitByInstitute($pageText);

        foreach ($sections as $section) {
            $sectionResults = $this->parseInstituteSection(
                $section['text'],
                $section['center_code'],
                $section['institute_name'],
                $semester,
                $regulation,
                $holdingYear,
                $lastDetectedDept,
                $isRescrutiny
            );
            $results = array_merge($results, $sectionResults);
        }

        return $results;
    }

    /**
     * Split page text into sections by institute center code boundaries.
     * Each section starts with a 5-digit center code.
     */
    private function splitByInstitute(string $pageText): array
    {
        // Find all center code positions: 5-digit code, optionally followed by " - Institute Name"
        preg_match_all('/(?:^|\n)(\d{5})\s*-\s*([^\n]*)/m', $pageText, $codeMatches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        if (empty($codeMatches)) {
            return [['text' => $pageText, 'center_code' => null, 'institute_name' => null]];
        }

        $sections = [];
        for ($i = 0; $i < count($codeMatches); $i++) {
            $centerCode = $codeMatches[$i][1][0] ?? null;
            $instituteName = trim($codeMatches[$i][2][0] ?? '');
            $startOffset = $codeMatches[$i][0][1];

            // Section ends at the next center code or end of text
            $endOffset = ($i + 1 < count($codeMatches)) ? $codeMatches[$i + 1][0][1] : strlen($pageText);

            $sectionText = substr($pageText, $startOffset, $endOffset - $startOffset);

            $sections[] = [
                'text' => $sectionText,
                'center_code' => $centerCode,
                'institute_name' => $instituteName,
            ];
        }

        return $sections;
    }

    /**
     * Parse a single institute section from a page.
     */
    private function parseInstituteSection(
        string $sectionText,
        ?string $centerCode,
        ?string $instituteName,
        string $semester,
        string $regulation,
        string $holdingYear,
        &$lastDetectedDept,
        bool $isRescrutiny
    ): array {
        $results = [];

        // Skip empty sections or sections without recognizable data
        if (strlen($sectionText) < 10) return $results;

        // Skip ALLIED/MARINE TRADE files (handled separately, no CMPI data)
        // But only if center code indicates it's not a regular polytechnic
        // We process ALL sections now — even Marine and ALLIED

        // Trim to next institute if text is too long (page may have multiple institutes)
        $trimmedText = $sectionText;
        if (preg_match('/\b\d{5}\s*-\s*/', substr($sectionText, 10), $nextInstMatch, PREG_OFFSET_CAPTURE)) {
            $trimmedText = substr($sectionText, 0, $nextInstMatch[0][1] + 10);
        }

        // Split text by semester headers to detect which results belong to which semester
        // BTEB PDFs often contain multiple semesters in one file
        // e.g. "1st Semester", "2nd Semester", "Semester: 1", "Sem 2", etc.
        $semesterChunks = $this->splitBySemesterHeader($trimmedText, $semester);

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
                    } elseif (stripos($deptName, "marine") !== false) {
                        $deptName = "Marine Technology";
                    } elseif (stripos($deptName, "mechanical") !== false) {
                        $deptName = "Mechanical Technology";
                    } elseif (stripos($deptName, "electronics") !== false) {
                        $deptName = "Electronics Technology";
                    } elseif (stripos($deptName, "telecom") !== false) {
                        $deptName = "Telecommunications Technology";
                    }

                    $techBlocks[] = [
                        'dept' => $deptName,
                        'text' => substr($chunkText, $start, $end - $start)
                    ];
                }
            }

            $chunkSemDigit = null;
            if (preg_match('/\d/', $chunkSemester, $m)) {
                $chunkSemDigit = $m[0];
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
                                'exam_type' => $isRescrutiny ? 'rescrutiny' : 'regular',
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
                                'exam_type' => $isRescrutiny ? 'rescrutiny' : 'regular',
                            ];
                    }
                }

                // Standard format: roll ( gpa ) or roll ( gpaN: X.XX, ... )
                preg_match_all('/\b(\d{6})\s*\(\s*([^)]+)\s*\)/', $blockText, $passedMatches, PREG_SET_ORDER);
                foreach ($passedMatches as $match) {
                    $roll = $match[1];
                    $contentStr = $match[2];

                    // Check for multi-GPA combined format in parentheses:
                    // 885565 (gpa4: 3.13, gpa3: 3.33, gpa2: 3.05, gpa1: 3.43)
                    $parenMultiGpa = preg_match_all('/gpa(\d)\s*:\s*([2-4]\.\d{2})/i', $contentStr, $parenSemMatches, PREG_SET_ORDER);

                    if ($parenMultiGpa > 1) {
                        // Split into per-semester records
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
                                'exam_type' => $isRescrutiny ? 'rescrutiny' : 'regular',
                            ];
                        }
                    } else {
                        // Single GPA — use original logic
                        $gpa = null;
                        if (preg_match('/^[2-4]\.\d{2}$/', trim($contentStr))) {
                            $gpa = (float)trim($contentStr);
                        } elseif ($chunkSemDigit !== null && preg_match('/gpa' . $chunkSemDigit . '\s*:\s*([2-4]\.\d{2})/i', $contentStr, $gpaMatches)) {
                            $gpa = (float)$gpaMatches[1];
                        } elseif (preg_match('/gpa\d+\s*:\s*([2-4]\.\d{2})/i', $contentStr, $gpaMatches)) {
                            $gpa = (float)$gpaMatches[1];
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
                            'exam_type' => $isRescrutiny ? 'rescrutiny' : 'regular',
                        ];
                    }
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
                                'exam_type' => $isRescrutiny ? 'rescrutiny' : 'regular',
                            ];
                        }
                        continue;
                    }

                    // Detect combined multi-semester format:
                    // 885530 { gpa4: ref, gpa3: 3.46, gpa2: 3.28, gpa1: 3.43, ref_sub: 26442(T), 26444(T), 26445(T) }
                    $hasMultiGpa = preg_match_all('/gpa(\d)\s*:\s*(ref|[2-4]\.\d{2})/i', $contentStr, $multiGpaMatches, PREG_SET_ORDER);

                    if ($hasMultiGpa && count($multiGpaMatches) > 1) {
                        // Extract ref subjects once
                        preg_match_all('/ref_sub\s*:\s*([^\}]+)/i', $contentStr, $refSubMatch);
                        $refSubjectsRaw = $refSubMatch[1][0] ?? '';
                        preg_match_all('/\b\d{5,6}(?:\([^)]+\))?\b/', $refSubjectsRaw, $refCodeMatches);
                        $allReferredSubjects = array_values(array_filter(array_map('trim', $refCodeMatches[0] ?? [])));

                        $inferredDept = $this->detectDeptFromSubjects($allReferredSubjects, '');
                        $studentDept = $inferredDept !== '' ? $inferredDept : $dept;

                        // Split referred subjects by semester using curriculum map
                        $semesterMap = \App\Utils\BtebSubjectSemesterMap::splitBySemester($allReferredSubjects, $studentDept);

                        // Create separate record per semester
                        foreach ($multiGpaMatches as $gpaMatch) {
                            $semDigit = $gpaMatch[1];
                            $semValue = $gpaMatch[2];
                            $suffix = match ((int)$semDigit) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };
                            $semLabel = $semDigit . $suffix;

                            if (strtolower($semValue) === 'ref') {
                                // Use semester-specific referred subjects from the map
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
                                    'exam_type' => $isRescrutiny ? 'rescrutiny' : 'regular',
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
                                'exam_type' => $isRescrutiny ? 'rescrutiny' : 'regular',
                            ];
                            }
                        }
                    } else {
                        // Single referred entry (old format)
                        preg_match_all('/\b\d{5,6}(?:\([^)]+\))?\b/', $contentStr, $codeMatches);
                        $allCodes = array_filter(array_map('trim', $codeMatches[0] ?? []));

                        $gpa = null;
                        if ($chunkSemDigit !== null && preg_match('/gpa' . $chunkSemDigit . '\s*:\s*([2-4]\.\d{2})/i', $contentStr, $gpaMatches)) {
                            $gpa = (float)$gpaMatches[1];
                        } elseif (preg_match('/gpa\d+\s*:\s*([2-4]\.\d{2})/i', $contentStr, $gpaMatches)) {
                            $gpa = (float)$gpaMatches[1];
                        } elseif (preg_match('/([2-4]\.\d{2})/', $contentStr, $gpaMatches)) {
                            $gpa = (float)$gpaMatches[1];
                        }

                        $inferredDept = $this->detectDeptFromSubjects(array_values($allCodes), '');
                        $studentDept = $inferredDept !== '' ? $inferredDept : $dept;

                        // Filter out GPA values from referred subjects (5-6 digit codes only)
                        $referredSubjects = array_values(array_filter($allCodes, function ($code) {
                            return preg_match('/^\d{5,6}(?:\([^)]+\))?$/', $code);
                        }));

                        // If a GPA was found, this student PASSED — no referred subjects
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
                                    'exam_type' => $isRescrutiny ? 'rescrutiny' : 'regular',
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
                                    'exam_type' => $isRescrutiny ? 'rescrutiny' : 'regular',
                                ];
                            }
                    }
                }
            }
        }

        if (!empty($results)) {
            $last = end($results);
            if ($last['department'] !== "Auto Detect" && $last['department'] !== "General Technology") {
                $lastDetectedDept = $last['department'];
            }
            // Add institute info to all results from this section
            foreach ($results as &$r) {
                $r['center_code'] = $centerCode;
                $r['institute_name'] = $instituteName;
            }
            unset($r);
        }

        return $results;
    }

    /**
     * Split page text by semester headers to detect which results belong to which semester.
     * BTEB result PDFs contain 1st, 2nd, 3rd etc. semester results in sections.
     * Patterns: "1st Semester", "2nd Semester", "Semester 1", "Sem: 2", "SEM-I", etc.
     */
    private function splitBySemesterHeader(string $text, string $defaultSemester): array
    {
        // Match various semester header patterns in BTEB PDFs
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
                // Roman numeral conversion
                $romanMap = ['I' => '1', 'II' => '2', 'III' => '3', 'IV' => '4', 'V' => '5', 'VI' => '6', 'VII' => '7', 'VIII' => '8', 'IX' => '9'];
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

        // If there's text before the first header, assign it the default semester
        $firstHeaderOffset = $matches[0][0][1];
        if ($firstHeaderOffset > 0) {
            array_unshift($chunks, [
                'semester' => $defaultSemester,
                'text' => substr($text, 0, $firstHeaderOffset)
            ]);
        }

        return $chunks;
    }

    /**
     * Resolve department by looking up every referred subject code in the BTEB
     * subject dictionary. Mirrors client/src/utils/btebSubjectCodes.ts exactly.
     * Source: Official BTEB Probidhan-2022 & Probidhan-2016 curricula.
     * Department codes: Civil=64, CST=65/85, Electrical=67, Electronics=68
     */
    private function detectDeptFromSubjects(array $subjects, string $defaultDept): string
    {
        // code => department (General subjects excluded â€” they don't help identify dept)
        $dict = [
            // â”€â”€ Civil Technology (64) â€” 2022 regulation (264xx) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
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
            // â”€â”€ Civil Technology (64) â€” 2016 regulation (664xx) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
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
            // â”€â”€ CST (65/85) â€” 2022 regulation (285xx) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
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
            // â”€â”€ CST (65/85) â€” 2016 regulation (666xx) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
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
            // â”€â”€ Electrical Technology (67) â€” 2022 regulation (267xx/268xx) â”€â”€â”€
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
            // â”€â”€ Electrical Technology (67) â€” 2016 regulation (667xx) â”€â”€â”€â”€â”€â”€â”€â”€â”€
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
            // â”€â”€ Electronics Technology (68) â€” 2016 regulation (668xx) â”€â”€â”€â”€â”€â”€â”€â”€
            '66841' => 'Electronics Technology', '66843' => 'Electronics Technology',
            '66851' => 'Electronics Technology', '66852' => 'Electronics Technology',
            '66853' => 'Electronics Technology', '66854' => 'Electronics Technology',
            '66855' => 'Electronics Technology', '66861' => 'Electronics Technology',
            '66862' => 'Electronics Technology', '66864' => 'Electronics Technology',
            '66865' => 'Electronics Technology', '66871' => 'Electronics Technology',
            '66872' => 'Electronics Technology', '66873' => 'Electronics Technology',
            '66874' => 'Electronics Technology', '66881' => 'Electronics Technology',
            '68643' => 'Electronics Technology', '68661' => 'Electronics Technology',
            // â”€â”€ Telecommunications Technology (70/71) â€” 2016 (670xx/671xx) â”€â”€â”€
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

            // —— Automobile Technology (62) ——
            '26241' => 'Automobile Technology',
            '66241' => 'Power Technology',
            '66253' => 'Power Technology',
            '66274' => 'Power Technology',

            // —— Chemical Technology (63) ——
            '26355' => 'Chemical Technology',
            '26364' => 'Chemical Technology',
            '26365' => 'Chemical Technology',
            '66363' => 'Chemical Technology',

            // —— Civil Technology (64) ——
            '26447' => 'Civil Technology',
            '28861' => 'Civil Technology',
            '66467' => 'Civil Technology',

            // —— Electrical Technology (67) ——
            '26762' => 'Electrical Technology',
            '26764' => 'Electrical Technology',
            '26771' => 'Electrical Technology',
            '26773' => 'Electrical Technology',
            '66743' => 'Electrical Technology',
            '66764' => 'Electrical Technology',

            // —— Electronics Technology (68) ——
            '26821' => 'Electronics Technology',
            '26831' => 'Electronics Technology',
            '26832' => 'Electronics Technology',
            '26841' => 'Electronics Technology',
            '26843' => 'Electronics Technology',
            '26851' => 'Electronics Technology',
            '26852' => 'Electronics Technology',
            '26861' => 'Electronics Technology',
            '26862' => 'Electronics Technology',
            '68662' => 'Electronics Technology',
            '68663' => 'Electronics Technology',
            '68665' => 'Electronics Technology',
            '68671' => 'Electronics Technology',
            '68672' => 'Electronics Technology',

            // —— Food Technology (69) ——
            '26932' => 'Food Technology',
            '26952' => 'Food Technology',
            '26954' => 'Food Technology',
            '26961' => 'Food Technology',
            '26963' => 'Food Technology',
            '26964' => 'Food Technology',
            '66944' => 'Food Technology',
            '66973' => 'Food Technology',

            // —— Mechanical Technology (70) ——
            '27021' => 'Mechanical Technology',
            '27042' => 'Mechanical Technology',
            '27043' => 'Mechanical Technology',
            '27053' => 'Mechanical Technology',
            '27055' => 'Mechanical Technology',
            '27062' => 'Mechanical Technology',
            '27063' => 'Mechanical Technology',
            '27065' => 'Mechanical Technology',
            '27073' => 'Mechanical Technology',
            '29231' => 'Mechanical Technology',
            '67052' => 'Mechanical Technology',
            '67053' => 'Mechanical Technology',
            '67076' => 'Mechanical Technology',

            // —— Power Technology (71) ——
            '27111' => 'Power Technology',
            '27151' => 'Power Technology',
            '27152' => 'Power Technology',
            '67142' => 'Power Technology',
            '67153' => 'Power Technology',
            '67162' => 'Power Technology',
            '67172' => 'Power Technology',

            // —— RAC Technology (72) ——
            '27231' => 'RAC Technology',
            '27252' => 'RAC Technology',
            '27261' => 'RAC Technology',

            // —— Marine Technology (79) ——
            '27941' => 'Marine Technology',
            '27942' => 'Marine Technology',
            '27954' => 'Marine Technology',
            '27961' => 'Marine Technology',
            '27962' => 'Marine Technology',
            '27963' => 'Marine Technology',
            '27964' => 'Marine Technology',
            '27965' => 'Marine Technology',

            // —— Shipbuilding Technology (80) ——
            '28021' => 'Shipbuilding Technology',
            '28061' => 'Shipbuilding Technology',
            '68081' => 'Shipbuilding Technology',

            // —— CST updates (85) ——
            '28533' => 'Computer Science & Technology',
            '28567' => 'Computer Science & Technology',
            '28572' => 'Computer Science & Technology',
            '28573' => 'Computer Science & Technology',
            '28575' => 'Computer Science & Technology',

            // —— Electromedical Technology (86) ——
            '28621' => 'Electromedical Technology',
            '28632' => 'Electromedical Technology',
            '28641' => 'Electromedical Technology',
            '28651' => 'Electromedical Technology',
            '28652' => 'Electromedical Technology',
            '28653' => 'Electromedical Technology',
            '28654' => 'Electromedical Technology',
            '28661' => 'Electromedical Technology',

            // —— Architecture Technology (88) ——
            '68774' => 'Architecture Technology',
        ];

        // Count how many referred subjects map to each department
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

        // Return department with most matching subject codes
        arsort($counts);
        return (string) array_key_first($counts);
    }
}
