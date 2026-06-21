<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\User;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'student') {
            return Bill::where('user_id', $user->id)->orderByDesc('created_at')->get();
        }

        // Admin: allow filtering by user
        $query = Bill::with('user');
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        return $query->orderByDesc('created_at')->get();
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'due' => 'required|date',
            'academic_year' => 'nullable|string',
        ]);

        $bill = Bill::create($data);

        return response()->json(['bill' => $bill], 201);
    }

    public function update(Request $request, Bill $bill)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $data = $request->validate([
            'title' => 'sometimes|string',
            'description' => 'sometimes|nullable|string',
            'type' => 'sometimes|string',
            'amount' => 'sometimes|numeric|min:0',
            'due' => 'sometimes|date',
            'academic_year' => 'sometimes|nullable|string',
            'status' => 'sometimes|in:pending,paid,overdue',
        ]);

        $bill->update($data);

        if (isset($data['status']) && $data['status'] === 'paid' && !$bill->paid_at) {
            $bill->update(['paid_at' => now()]);
        }

        return response()->json(['bill' => $bill]);
    }

    public function destroy(Request $request, Bill $bill)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $bill->delete();

        return response()->json(['message' => 'Bill deleted successfully']);
    }

    public function markPaid(Request $request, Bill $bill)
    {
        $user = $request->user();

        if ($user->role !== 'admin' && $bill->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'payment_method' => 'nullable|string',
            'transaction_id' => 'nullable|string',
        ]);

        $bill->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id,
        ]);

        return response()->json(['bill' => $bill]);
    }

    public function stats(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $totalPending = Bill::where('status', 'pending')->sum('amount');
        $totalPaid = Bill::where('status', 'paid')->sum('amount');
        $totalOverdue = Bill::where('status', 'overdue')->sum('amount');
        $countPending = Bill::where('status', 'pending')->count();
        $countPaid = Bill::where('status', 'paid')->count();

        return response()->json([
            'total_pending' => $totalPending,
            'total_paid' => $totalPaid,
            'total_overdue' => $totalOverdue,
            'count_pending' => $countPending,
            'count_paid' => $countPaid,
        ]);
    }

    public function bulkCreate(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $data = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'due' => 'required|date',
            'academic_year' => 'nullable|string',
        ]);

        $bills = [];
        foreach ($data['user_ids'] as $userId) {
            $bills[] = Bill::create([
                'user_id' => $userId,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'type' => $data['type'],
                'amount' => $data['amount'],
                'due' => $data['due'],
                'academic_year' => $data['academic_year'] ?? null,
            ]);
        }

        return response()->json([
            'message' => count($bills) . ' bills created successfully',
            'count' => count($bills),
        ], 201);
    }
}
