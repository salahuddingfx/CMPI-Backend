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
}