<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\CourseResult;
use App\Models\Bill;
use App\Models\Email;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();

        return [
            'user' => $user,
            'courses' => $user->courses,
            'results' => $user->courseResults,
            'bills' => $user->bills,
        ];
    }

    public function courses(Request $request)
    {
        return $request->user()->courses;
    }

    public function results(Request $request)
    {
        return $request->user()->courseResults;
    }

    public function bills(Request $request)
    {
        return $request->user()->bills;
    }

    public function profile(Request $request)
    {
        return $request->user();
    }

    public function emails(Request $request)
    {
        $email = $request->user()->email;

        return Email::where('to_email', $email)
            ->orWhere('from_email', $email)
            ->orWhere('to_email', 'all-students@cmpi.edu.bd')
            ->orderByDesc('date')
            ->get();
    }
}