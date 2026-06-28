<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InstituteEvent;
use App\Models\EventRegistration;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function index()
    {
        return InstituteEvent::orderByDesc('date')->paginate(15);
    }

    public function show(InstituteEvent $event)
    {
        return $event;
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin role required.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'end_date' => 'nullable|date',
            'time' => 'required|string',
            'venue' => 'required|string',
            'category' => 'required|string',
            'status' => 'required|string|in:Upcoming,Past',
            'summary' => 'required|string',
            'details' => 'required|string',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event = InstituteEvent::create($request->all());
        return response()->json($event, 201);
    }

    public function update(Request $request, InstituteEvent $event)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin role required.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'end_date' => 'nullable|date',
            'time' => 'required|string',
            'venue' => 'required|string',
            'category' => 'required|string',
            'status' => 'required|string|in:Upcoming,Past',
            'summary' => 'required|string',
            'details' => 'required|string',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event->update($request->all());
        return response()->json($event);
    }

    public function destroy(Request $request, InstituteEvent $event)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin role required.'], 403);
        }

        $event->delete();
        return response()->json(['message' => 'Event deleted successfully']);
    }

    public function register(Request $request, InstituteEvent $event)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'roll_no' => 'required|string|max:50',
            'department' => 'required|string|max:255',
            'semester' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $registration = EventRegistration::create(array_merge(
            $validator->validated(),
            ['event_id' => $event->id]
        ));

        // Create administrative alert notification
        Notification::create([
            'title' => 'New Event Registration',
            'description' => $registration->name . " registered for the event: " . $event->title . " (" . $registration->department . ", " . $registration->semester . ")",
            'type' => 'success',
            'is_read' => false,
        ]);

        return response()->json([
            'message' => 'Registration successful! See you at the event.',
            'registration' => $registration,
        ], 201);
    }
}