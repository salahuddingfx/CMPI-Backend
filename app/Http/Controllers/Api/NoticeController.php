<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notice;

class NoticeController extends Controller
{
    public function index()
    {
        return Notice::orderByDesc('date')->get();
    }

    public function show(Notice $notice)
    {
        return $notice;
    }
}