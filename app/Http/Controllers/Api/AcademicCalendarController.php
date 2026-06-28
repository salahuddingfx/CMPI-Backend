<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicCalendar;
use Illuminate\Http\Request;

class AcademicCalendarController extends Controller
{
    /**
     * GET /academic-calendar
     * Supports optional ?year=2026&month=6 filters.
     */
    public function index(Request $request)
    {
        $query = AcademicCalendar::orderBy('event_date');

        if ($request->has('year')) {
            $query->whereYear('event_date', (int) $request->year);
        }
        if ($request->has('month')) {
            $query->whereMonth('event_date', (int) $request->month);
        }
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        return response()->json($query->get());
    }

    /**
     * GET /academic-calendar/{id}
     */
    public function show(int $id)
    {
        return response()->json(AcademicCalendar::findOrFail($id));
    }

    /**
     * POST /academic-calendar (admin)
     */
    public function store(Request $request)
    {
        if (!$request->user()->hasPermission('academic_calendar')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'event_date'  => 'required|date',
            'end_date'    => 'nullable|date|after_or_equal:event_date',
            'category'    => 'required|in:exam,holiday,event,meeting,deadline,other',
            'description' => 'nullable|string|max:1000',
            'is_holiday'  => 'boolean',
        ]);

        return response()->json(AcademicCalendar::create($data), 201);
    }

    /**
     * PUT /academic-calendar/{id} (admin)
     */
    public function update(Request $request, int $id)
    {
        if (!$request->user()->hasPermission('academic_calendar')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $calendar = AcademicCalendar::findOrFail($id);

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'event_date'  => 'required|date',
            'end_date'    => 'nullable|date|after_or_equal:event_date',
            'category'    => 'required|in:exam,holiday,event,meeting,deadline,other',
            'description' => 'nullable|string|max:1000',
            'is_holiday'  => 'boolean',
        ]);

        $calendar->update($data);
        return response()->json($calendar);
    }

    /**
     * DELETE /academic-calendar/{id} (admin)
     */
    public function destroy(Request $request, int $id)
    {
        if (!$request->user()->hasPermission('academic_calendar')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        AcademicCalendar::findOrFail($id)->delete();
        return response()->json(['message' => 'Event deleted successfully.']);
    }
}
