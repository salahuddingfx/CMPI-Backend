<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth-token')->expiresAt(now()->addHours(24))->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|regex:/[A-Z]/|regex:/[a-z]/|regex:/[0-9]/',
            'department' => 'nullable|string',
            'student_id' => 'nullable|string|unique:users,student_id',
            'semester' => 'nullable|string',
            'session' => 'nullable|string',
            'phone' => 'nullable|string',
            'guardian' => 'nullable|string',
            'blood_group' => 'nullable|string',
            'address' => 'nullable|string',
            'admission_date' => 'nullable|date',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'department' => $request->department,
            'student_id' => $request->student_id,
            'semester' => $request->semester ?? '1st',
            'session' => $request->session,
            'phone' => $request->phone,
            'guardian' => $request->guardian,
            'blood_group' => $request->blood_group,
            'address' => $request->address,
            'admission_date' => $request->admission_date,
            'role' => 'student',
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Registration successful! Your account is pending admin approval.',
            'user' => $user
        ], 201);
    }
}