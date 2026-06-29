<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\CourseResult;
use App\Models\Bill;
use App\Models\Email;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();

        $boardResults = collect();
        if ($user->board_roll) {
            $boardResults = \App\Models\BtebResult::where('roll', $user->board_roll)->get();
        }

        return [
            'user' => $user,
            'courses' => $user->courses,
            'results' => $user->courseResults,
            'board_results' => $boardResults,
            'bills' => $user->bills,
        ];
    }

    public function courses(Request $request)
    {
        return $request->user()->courses;
    }

    public function results(Request $request)
    {
        $user = $request->user();
        $courseResults = $user->courseResults;
        
        $boardResults = collect();
        if ($user->board_roll) {
            $boardResults = \App\Models\BtebResult::where('roll', $user->board_roll)->get();
        }

        return response()->json([
            'course_results' => $courseResults,
            'board_results' => $boardResults,
        ]);
    }

    public function bills(Request $request)
    {
        return $request->user()->bills;
    }

    public function profile(Request $request)
    {
        return $request->user();
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => 'sometimes|string',
            'phone' => 'sometimes|string',
            'guardian' => 'sometimes|string',
            'blood_group' => 'sometimes|string|nullable',
            'address' => 'sometimes|string',
            'avatar' => 'nullable|string',
            'board_roll' => 'sometimes|string|nullable',
            'reg_no' => 'sometimes|string|nullable',
            'student_id' => 'sometimes|string|nullable',
            'department' => 'sometimes|string|nullable',
            'semester' => 'sometimes|string|nullable',
            'session' => 'sometimes|string|nullable',
        ]);

        $user->update($data);

        return response()->json(['user' => $user]);
    }

    public function emails(Request $request)
    {
        $user = $request->user();
        $emailAddress = $user->email;

        // Try live IMAP fetching if password is cached
        $enc = \Illuminate\Support\Facades\Cache::get("user_pass_" . $user->id);
        if ($enc) {
            try {
                $password = decrypt($enc);
                $imap = new \App\Services\ImapClient();
                $result = $imap->fetchInbox($emailAddress, $password);
                
                if ($result['status'] === 'success') {
                    return response()->json($result['emails']);
                }
            } catch (\Exception $e) {
                // Fail silently and fallback to local DB emails
            }
        }

        // Fallback to local database emails
        $dbEmails = Email::where('to_email', $emailAddress)
            ->orWhere('from_email', $emailAddress)
            ->orWhere('to_email', 'all-students@cmpi.edu.bd')
            ->orderByDesc('date')
            ->get();

        return response()->json($dbEmails);
    }

    public function emailBody(Request $request, $id)
    {
        $user = $request->user();
        
        // If it's a live IMAP email, fetch details
        if (str_starts_with($id, 'live_')) {
            $msgId = (int)str_replace('live_', '', $id);
            $enc = \Illuminate\Support\Facades\Cache::get("user_pass_" . $user->id);
            if ($enc) {
                try {
                    $password = decrypt($enc);
                    $imap = new \App\Services\ImapClient();
                    $body = $imap->fetchMessageBody($user->email, $password, $msgId);
                    return response()->json(['body' => $body]);
                } catch (\Exception $e) {
                    return response()->json(['body' => 'Failed to load live email body.'], 500);
                }
            }
        }

        // Fallback/Local email lookup - ensure email belongs to user
        $dbEmail = Email::where('id', $id)->where('user_id', $user->id)->first();
        return response()->json([
            'body' => $dbEmail ? $dbEmail->body : 'Email body not found.'
        ]);
    }

    public function allUsers(Request $request)
    {
        if ($request->user()->role !== 'admin' || (!empty($request->user()->sub_role) && $request->user()->sub_role !== 'super_admin')) {
            return response()->json(['message' => 'Unauthorized. Super Admin access required.'], 403);
        }
        return User::all();
    }

    public function storeUser(Request $request)
    {
        if ($request->user()->role !== 'admin' || (!empty($request->user()->sub_role) && $request->user()->sub_role !== 'super_admin')) {
            return response()->json(['message' => 'Unauthorized. Super Admin access required.'], 403);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'department' => 'nullable|string',
            'student_id' => 'nullable|string|unique:users,student_id',
            'board_roll' => 'nullable|string',
            'reg_no' => 'nullable|string',
            'semester' => 'nullable|string',
            'session' => 'nullable|string',
            'phone' => 'nullable|string',
            'guardian' => 'nullable|string',
            'blood_group' => 'nullable|string',
            'address' => 'nullable|string',
            'admission_date' => 'nullable|date',
            'role' => 'required|string|in:student,admin',
            'sub_role' => 'nullable|string|in:super_admin,academic_editor,content_manager,admission_officer,accountant',
            'status' => 'nullable|string|in:pending,active,suspended',
            'avatar' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['password'] = \Illuminate\Support\Facades\Hash::make($request->password);

        $user = User::create($data);
        return response()->json($user, 201);
    }

    public function updateUser(Request $request, User $user)
    {
        if ($request->user()->role !== 'admin' || (!empty($request->user()->sub_role) && $request->user()->sub_role !== 'super_admin')) {
            return response()->json(['message' => 'Unauthorized. Super Admin access required.'], 403);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'department' => 'nullable|string',
            'student_id' => 'nullable|string|unique:users,student_id,' . $user->id,
            'board_roll' => 'nullable|string',
            'reg_no' => 'nullable|string',
            'semester' => 'nullable|string',
            'session' => 'nullable|string',
            'phone' => 'nullable|string',
            'guardian' => 'nullable|string',
            'blood_group' => 'nullable|string',
            'address' => 'nullable|string',
            'admission_date' => 'nullable|date',
            'role' => 'required|string|in:student,admin',
            'sub_role' => 'nullable|string|in:super_admin,academic_editor,content_manager,admission_officer,accountant',
            'status' => 'nullable|string|in:pending,active,suspended',
            'avatar' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        if (!empty($request->password)) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        return response()->json($user);
    }

    public function destroyUser(Request $request, User $user)
    {
        if ($request->user()->role !== 'admin' || (!empty($request->user()->sub_role) && $request->user()->sub_role !== 'super_admin')) {
            return response()->json(['message' => 'Unauthorized. Super Admin access required.'], 403);
        }

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Cannot delete yourself'], 400);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function bulkImport(Request $request)
    {
        if ($request->user()->role !== 'admin' || (!empty($request->user()->sub_role) && $request->user()->sub_role !== 'super_admin')) {
            return response()->json(['message' => 'Unauthorized.'], 403);   
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());
        $lines = array_filter(explode("\n", $content));

        if (count($lines) < 2) {
            return response()->json(['message' => 'CSV file is empty or has no data rows'], 422);
        }

        $header = array_map('trim', str_getcsv(array_shift($lines)));
        $header = array_map('strtolower', $header);

        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($lines as $lineNum => $line) {
            $row = array_map('trim', str_getcsv($line));
            if (count($row) < 3) continue;

            $data = array_combine($header, $row);
            $name = $data['name'] ?? null;
            $email = $data['email'] ?? null;

            if (!$name || !$email) {
                $errors[] = "Line " . ($lineNum + 2) . ": Missing name or email";
                $skipped++;
                continue;
            }

            if (User::where('email', $email)->exists()) {
                $skipped++;
                continue;
            }

            $year = date('Y');
            $studentId = 'CMPI-' . $year . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            while (User::where('student_id', $studentId)->exists()) {
                $studentId = 'CMPI-' . $year . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }

            User::create([
                'name' => $name,
                'email' => $email,
                'password' => \Illuminate\Support\Facades\Hash::make($data['password'] ?? \Illuminate\Support\Str::random(16)),
                'student_id' => $studentId,
                'board_roll' => $data['board_roll'] ?? null,
                'reg_no' => $data['reg_no'] ?? null,
                'department' => $data['department'] ?? null,
                'semester' => $data['semester'] ?? '1st',
                'session' => $data['session'] ?? ($year . '-' . ($year + 1)),
                'phone' => $data['phone'] ?? null,
                'guardian' => $data['guardian'] ?? null,
                'blood_group' => $data['blood_group'] ?? null,
                'address' => $data['address'] ?? null,
                'role' => 'student',
            ]);
            $created++;
        }

        return response()->json([
            'message' => "Import complete: {$created} created, {$skipped} skipped",
            'created' => $created,
            'skipped' => $skipped,
        ]);
    }

    public function verifyPublic($studentId)
    {
        $user = User::where('student_id', $studentId)
            ->where('role', 'student')
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Student not found.'], 404);
        }

        return response()->json([
            'name' => $user->name,
            'student_id' => $user->student_id,
            'board_roll' => $user->board_roll,
            'reg_no' => $user->reg_no,
            'department' => $user->department,
            'semester' => $user->semester,
            'session' => $user->session,
            'blood_group' => $user->blood_group,
            'status' => $user->status,
            'avatar' => $user->avatar,
            'verified_at' => now()->toIso8601String(),
        ]);
    }

    public function downloadIdCard(Request $request)
    {
        $user = $request->user();

        // Get CMPI logo and base64-encode it
        $logoPath = public_path('CMPI.png');
        $logoSrc = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;
        }

        // Get avatar and base64-encode it
        $avatarSrc = '';
        if ($user->avatar) {
            $urlPath = parse_url($user->avatar, PHP_URL_PATH);
            $avatarPath = null;
            if (str_starts_with($urlPath, '/storage/')) {
                $avatarPath = public_path(substr($urlPath, 1));
            } else {
                $avatarPath = public_path($urlPath);
            }

            if ($avatarPath && file_exists($avatarPath)) {
                $avatarData = base64_encode(file_get_contents($avatarPath));
                $avatarMime = mime_content_type($avatarPath) ?: 'image/png';
                $avatarSrc = 'data:' . $avatarMime . ';base64,' . $avatarData;
            }
        }

        // Get QR code base64-encoded
        $verificationUrl = url("/verify-student/{$user->student_id}");
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($verificationUrl);
        $qrSrc = $qrUrl;
        try {
            // Fetch remote QR code via curl/file_get_contents
            $qrData = @file_get_contents($qrUrl);
            if ($qrData) {
                $qrSrc = 'data:image/png;base64,' . base64_encode($qrData);
            }
        } catch (\Exception $e) {
            // Fallback to QR server URL directly
        }

        $pdf = Pdf::loadView('reports.student-id-card', [
            'user' => $user,
            'logoSrc' => $logoSrc,
            'avatarSrc' => $avatarSrc,
            'qrSrc' => $qrSrc,
        ]);

        return $pdf->download("student-id-card-{$user->student_id}.pdf");
    }
}