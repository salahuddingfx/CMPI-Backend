<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InstituteEvent;

class EventController extends Controller
{
    public function index()
    {
        return InstituteEvent::orderByDesc('date')->get();
    }

    public function show(InstituteEvent $event)
    {
        return $event;
    }
}