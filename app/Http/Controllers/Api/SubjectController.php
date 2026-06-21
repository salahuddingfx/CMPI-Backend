<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Subject::query();

        if ($request->has('department')) {
            $query->where('department', $request->department);
        }

        if ($request->has('semester')) {
            $query->where('semester', $request->semester);
        }

        return $query->orderBy('department')->orderBy('semester')->orderBy('subject_code')->get();
    }
}
