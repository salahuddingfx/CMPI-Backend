<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CookieConsent;
use Illuminate\Http\Request;

class CookieConsentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'consent_type' => 'required|in:accept,deny',
            'email' => 'nullable|email',
        ]);

        $consent = CookieConsent::create([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'email' => $request->email,
            'consent_type' => $request->consent_type,
        ]);

        return response()->json($consent, 201);
    }

    public function index(Request $request)
    {
        $query = CookieConsent::latest();

        if ($request->consent_type) {
            $query->where('consent_type', $request->consent_type);
        }

        if ($request->email) {
            $query->where('email', 'like', "%{$request->email}%");
        }

        $consents = $query->paginate($request->per_page ?? 50);

        return response()->json($consents);
    }

    public function stats()
    {
        $total = CookieConsent::count();
        $accepted = CookieConsent::where('consent_type', 'accept')->count();
        $denied = CookieConsent::where('consent_type', 'deny')->count();

        return response()->json([
            'total' => $total,
            'accepted' => $accepted,
            'denied' => $denied,
        ]);
    }
}
