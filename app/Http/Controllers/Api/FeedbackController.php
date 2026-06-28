<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index()
    {
        return Feedback::with('user:id,name')
            ->orderByDesc('created_at')
            ->paginate(20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'category'    => 'required|in:general,academic,facility,hostel,other',
            'description' => 'required|string|max:2000',
        ]);

        if ($request->user()) {
            $data['user_id'] = $request->user()->id;
        }

        return response()->json(Feedback::create($data), 201);
    }

    public function upvote(Feedback $feedback)
    {
        $feedback->increment('upvotes');
        return $feedback;
    }

    public function approve(Request $request, Feedback $feedback)
    {
        if (!$request->user()->hasPermission('feedbacks')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        $feedback->update(['status' => 'in-progress']);
        return response()->json($feedback);
    }

    public function reject(Request $request, Feedback $feedback)
    {
        if (!$request->user()->hasPermission('feedbacks')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        $feedback->update(['status' => 'resolved']);
        return response()->json($feedback);
    }

    public function destroy(Request $request, Feedback $feedback)
    {
        if (!$request->user()->hasPermission('feedbacks')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        $feedback->delete();
        return response()->json(['message' => 'Feedback deleted successfully.']);
    }
}