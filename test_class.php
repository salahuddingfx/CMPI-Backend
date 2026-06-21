<?php
$code = file_get_contents(__DIR__ . '/app/Http/Controllers/Api/BtebResultController.php');
$lines = explode("\n", $code);

// Create a minimal class with just the methods
$minimal = '<?php

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
    public function search(Request $request) {
        $results = BtebResult::where("roll", $request->query("roll"))->get();
        return response()->json($results);
    }
    
    public function import(Request $request) {
        return response()->json(["message" => "ok"]);
    }
    
    public function importFromDrive(Request $request) {
        return response()->json(["message" => "ok"]);
    }
    
    public function importStatus(Request $request, string $jobId) {
        return response()->json(["message" => "ok"]);
    }
    
    public function uploadPdf(Request $request) {
        return response()->json(["message" => "ok"]);
    }
    
    private function splitBySemesterHeader(string $text, string $defaultSemester): array {
        return [["semester" => $defaultSemester, "text" => $text]];
    }
    
    private function detectDeptFromSubjects(array $subjects, string $defaultDept): string {
        return $defaultDept;
    }
}';

file_put_contents(__DIR__ . '/temp_minimal.php', $minimal);
exec('php -l ' . escapeshellarg(__DIR__ . '/temp_minimal.php') . ' 2>&1', $out, $ret);
echo "Minimal class: " . ($ret === 0 ? "OK" : "FAIL") . "\n";
if ($ret !== 0) echo "  " . $out[0] . "\n";

// Now test: add back each method body gradually
echo "\nNow let me check the original file line by line...\n";

// Find the exact problem by checking syntax on smaller chunks
// Test lines 522-724 (the last two methods)
$chunk = '<?php
namespace App\Http\Controllers\Api;
class Test {
' . implode("\n", array_slice($lines, 520)) . '
}';

file_put_contents(__DIR__ . '/temp_chunk.php', $chunk);
exec('php -l ' . escapeshellarg(__DIR__ . '/temp_chunk.php') . ' 2>&1', $out, $ret);
echo "Chunk lines 522-724: " . ($ret === 0 ? "OK" : "FAIL") . "\n";
if ($ret !== 0) echo "  " . $out[0] . "\n";
