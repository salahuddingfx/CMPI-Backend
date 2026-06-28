<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminMessageController extends Controller
{
    public function index()
    {
        $messages = AdminMessage::all();
        return response()->json($messages);
    }

    public function update(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin role required.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'message' => 'required|string',
            'avatar' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $message = AdminMessage::find($id);

        if (!$message) {
            return response()->json(['message' => 'Message not found'], 404);
        }

        $message->update($request->all());

        return response()->json([
            'message' => 'Message updated successfully',
            'data' => $message
        ]);
    }
}
