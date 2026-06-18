<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index()
    {
        return Feedback::orderByDesc('created_at')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'category' => 'required|in:general,academic,facility,hostel,other',
            'description' => 'required|string',
        ]);

        if ($request->user()) {
            $data['user_id'] = $request->user()->id;
        }

        return Feedback::create($data);
    }

    public function upvote(Feedback $feedback)
    {
        $feedback->increment('upvotes');
        return $feedback;
    }
}