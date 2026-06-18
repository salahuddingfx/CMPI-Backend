<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use Illuminate\Http\Request;

class AdmissionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'department' => 'required|string',
            'ssc_gpa' => 'required|string',
            'father_name' => 'required|string',
            'mother_name' => 'required|string',
            'address' => 'required|string',
            'blood_group' => 'nullable|string',
        ]);

        $data['application_id'] = 'CMPI-ADM-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);

        $admission = Admission::create($data);

        return response()->json([
            'message' => 'Application submitted',
            'application_id' => $data['application_id'],
            'admission' => $admission,
        ], 201);
    }

    public function index()
    {
        return Admission::all();
    }
}