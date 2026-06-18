<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;

class DepartmentController extends Controller
{
    public function index()
    {
        return Department::all();
    }

    public function show(Department $department)
    {
        return $department;
    }

    public function bySlug($slug)
    {
        return Department::where('slug', $slug)->firstOrFail();
    }
}