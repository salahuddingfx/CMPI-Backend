<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faculty;

class FacultyController extends Controller
{
    public function index()
    {
        return Faculty::all();
    }

    public function show(Faculty $faculty)
    {
        return $faculty;
    }
}