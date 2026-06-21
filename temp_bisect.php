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

    
