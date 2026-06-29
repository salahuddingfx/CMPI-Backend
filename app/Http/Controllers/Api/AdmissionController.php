<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\User;
use App\Mail\AdmissionReceived;
use App\Mail\AdmissionApproved;
use App\Mail\AdmissionRejected;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AdmissionController extends Controller
{
    private array $requiredDocuments = [
        'ssc_certificate' => 'SSC Certificate + Marksheet',
        'nid_birth' => 'NID / Birth Certificate',
        'photos' => '4 Passport-size Photographs',
        'guardian_nid' => "Guardian's NID",
        'transfer_cert' => 'Transfer Certificate',
        'character_cert' => 'Proshongso Potro (Character Certificate)',
    ];

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'department' => 'required|string',
            'session' => 'nullable|string',
            'ssc_gpa' => 'required|string',
            'hsc_gpa' => 'nullable|string',
            'father_name' => 'required|string',
            'mother_name' => 'required|string',
            'address' => 'required|string',
            'blood_group' => 'nullable|string',
            'payment_method' => 'required|string',
            'txn_id' => 'required|string',
            'sender_number' => 'required|string',
        ]);

        $data['application_id'] = 'CMPI-ADM-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $data['status'] = 'Pending';

        $documents = [];
        $disk = Storage::disk('local');

        foreach ($this->requiredDocuments as $key => $label) {
            if ($request->hasFile("doc_{$key}")) {
                $file = $request->file("doc_{$key}");
                $filename = $data['application_id'] . '_' . $key . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('admissions', $filename, 'local');
                $documents[$key] = [
                    'label' => $label,
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                ];
            }
        }

        $data['documents'] = count($documents) > 0 ? $documents : null;

        $admission = Admission::create($data);

        try {
            Mail::to($admission->email)->send(new AdmissionReceived($admission));
        } catch (\Exception $e) {
            // Don't fail the request if email fails
        }

        return response()->json([
            'message' => 'Application submitted',
            'application_id' => $data['application_id'],
            'admission' => $admission,
        ], 201);
    }

    public function track(Request $request)
    {
        $request->validate(['application_id' => 'required|string']);

        $admission = Admission::where('application_id', $request->application_id)->first();

        if (!$admission) {
            return response()->json(['error' => 'Application not found'], 404);
        }

        return response()->json([
            'application_id' => $admission->application_id,
            'name' => $admission->name,
            'department' => $admission->department,
            'session' => $admission->session,
            'status' => $admission->status,
            'payment_method' => $admission->payment_method,
            'txn_id' => $admission->txn_id,
            'payment_status' => $admission->payment_status,
            'sender_number' => $admission->sender_number,
            'admission_fee_amount' => $admission->admission_fee_amount,
            'admission_fee_status' => $admission->admission_fee_status,
            'board_confirmation' => $admission->board_confirmation,
            'created_at' => $admission->created_at,
            'documents' => $admission->documents ? array_keys($admission->documents) : [],
        ]);
    }

    public function downloadForm()
    {
        $pdf = new \Dompdf\Dompdf();

        $html = view('admissions.download-form')->render();

        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        $pdf->stream('CMPI_Admission_Form.pdf', ['Attachment' => true]);
    }

    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin role required.'], 403);
        }
        return Admission::orderByDesc('created_at')->get();
    }

    public function updateStatus(Request $request, Admission $admission)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin role required.'], 403);
        }

        $request->validate([
            'status' => 'required|string|in:Pending,Approved,Rejected',
            'payment_status' => 'nullable|string|in:unpaid,paid,pending_verification',
            'admission_fee_amount' => 'nullable|numeric',
            'admission_fee_status' => 'nullable|string|in:unpaid,paid,pending_verification',
            'board_confirmation' => 'nullable|string|in:pending,confirmed',
            'reason' => 'nullable|string',
        ]);

        $newStatus = $request->status;
        $admission->status = $newStatus;
        if ($request->has('payment_status')) {
            $admission->payment_status = $request->payment_status;
        }
        if ($request->has('admission_fee_amount')) {
            $admission->admission_fee_amount = $request->admission_fee_amount;
        }
        if ($request->has('admission_fee_status')) {
            $admission->admission_fee_status = $request->admission_fee_status;
        }
        if ($request->has('board_confirmation')) {
            $admission->board_confirmation = $request->board_confirmation;
        }
        $admission->save();

        if (strtolower($newStatus) === 'approved') {
            $userExists = User::where('email', $admission->email)->exists();
            if (!$userExists) {
                $year = date('Y');
                $studentId = 'CMPI-' . $year . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                while (User::where('student_id', $studentId)->exists()) {
                    $studentId = 'CMPI-' . $year . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                }

                User::create([
                    'name' => $admission->name,
                    'email' => $admission->email,
                    'password' => Hash::make(\Illuminate\Support\Str::random(16)),
                    'department' => $admission->department,
                    'student_id' => $studentId,
                    'semester' => '1st',
                    'session' => $admission->session ?? ($year . '-' . ($year + 1)),
                    'phone' => $admission->phone,
                    'guardian' => $admission->father_name . ' (Father)',
                    'blood_group' => $admission->blood_group ?? '-',
                    'address' => $admission->address,
                    'admission_date' => now(),
                    'role' => 'student',
                ]);

                try {
                    Mail::to($admission->email)->send(new AdmissionApproved($admission, $studentId));
                } catch (\Exception $e) {
                    // Don't fail the request if email fails
                }
            }
        } elseif (strtolower($newStatus) === 'rejected') {
            try {
                Mail::to($admission->email)->send(new AdmissionRejected($admission, $request->reason ?? null));
            } catch (\Exception $e) {
                // Don't fail the request if email fails
            }
        }

        return response()->json([
            'message' => 'Admission status updated successfully',
            'admission' => $admission,
        ]);
    }
}
