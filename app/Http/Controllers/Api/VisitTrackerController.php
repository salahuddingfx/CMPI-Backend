<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PageVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VisitTrackerController extends Controller
{
    public function track(Request $request)
    {
        $request->validate([
            'visitor_id' => 'nullable|string|max:36',
            'page_url' => 'nullable|string',
            'referrer' => 'nullable|string',
        ]);

        $ip = $request->ip();
        $geo = $this->geoLocate($ip);

        $ua = $request->userAgent();
        $device = $this->detectDevice($ua);
        $browser = $this->detectBrowser($ua);
        $os = $this->detectOs($ua);

        $visit = PageVisit::create([
            'visitor_id' => $request->visitor_id,
            'ip_address' => $ip,
            'country' => $geo['country'] ?? null,
            'city' => $geo['city'] ?? null,
            'region' => $geo['region'] ?? null,
            'isp' => $geo['isp'] ?? null,
            'page_url' => $request->page_url,
            'referrer' => $request->referrer,
            'user_agent' => $ua,
            'device_type' => $device,
            'browser' => $browser,
            'os' => $os,
        ]);

        return response()->json($visit, 201);
    }

    public function index(Request $request)
    {
        $query = PageVisit::latest();

        if ($request->country) {
            $query->where('country', $request->country);
        }
        if ($request->device_type) {
            $query->where('device_type', $request->device_type);
        }
        if ($request->page_url) {
            $query->where('page_url', 'like', "%{$request->page_url}%");
        }
        if ($request->visitor_id) {
            $query->where('visitor_id', $request->visitor_id);
        }
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $visits = $query->paginate($request->per_page ?? 50);

        return response()->json($visits);
    }

    public function stats()
    {
        $total = PageVisit::count();
        $uniqueVisitors = PageVisit::whereNotNull('visitor_id')->distinct('visitor_id')->count('visitor_id');
        $uniqueCountries = PageVisit::whereNotNull('country')->distinct('country')->count('country');

        $topPages = PageVisit::selectRaw('page_url, count(*) as visits')
            ->whereNotNull('page_url')
            ->groupBy('page_url')
            ->orderByDesc('visits')
            ->limit(10)
            ->get();

        $topCountries = PageVisit::selectRaw('country, count(*) as visits')
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderByDesc('visits')
            ->limit(10)
            ->get();

        $deviceBreakdown = PageVisit::selectRaw('device_type, count(*) as count')
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->get();

        $today = PageVisit::whereDate('created_at', today())->count();
        $thisWeek = PageVisit::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();

        return response()->json([
            'total' => $total,
            'unique_visitors' => $uniqueVisitors,
            'unique_countries' => $uniqueCountries,
            'today' => $today,
            'this_week' => $thisWeek,
            'top_pages' => $topPages,
            'top_countries' => $topCountries,
            'device_breakdown' => $deviceBreakdown,
        ]);
    }

    private function geoLocate(string $ip): array
    {
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return ['country' => 'Localhost', 'city' => null, 'region' => null, 'isp' => null];
        }

        try {
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=country,city,region,isp,query");
            if ($response->successful()) {
                $data = $response->json();
                if ($data && ($data['country'] ?? null)) {
                    return $data;
                }
            }
        } catch (\Exception $e) {
            // fall through
        }

        return [];
    }

    private function detectDevice(string $ua): string
    {
        if (preg_match('/Mobile|Android|iPhone|iPad|iPod/i', $ua)) {
            if (preg_match('/iPad|Tablet/i', $ua)) return 'tablet';
            return 'mobile';
        }
        return 'desktop';
    }

    private function detectBrowser(string $ua): string
    {
        if (str_contains($ua, 'Chrome') && !str_contains($ua, 'Edg')) return 'Chrome';
        if (str_contains($ua, 'Firefox')) return 'Firefox';
        if (str_contains($ua, 'Safari') && !str_contains($ua, 'Chrome')) return 'Safari';
        if (str_contains($ua, 'Edg')) return 'Edge';
        if (str_contains($ua, 'OPR') || str_contains($ua, 'Opera')) return 'Opera';
        return 'Other';
    }

    private function detectOs(string $ua): string
    {
        if (str_contains($ua, 'Windows')) return 'Windows';
        if (str_contains($ua, 'Mac OS') || str_contains($ua, 'macOS')) return 'macOS';
        if (str_contains($ua, 'Linux') && !str_contains($ua, 'Android')) return 'Linux';
        if (str_contains($ua, 'Android')) return 'Android';
        if (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) return 'iOS';
        return 'Other';
    }
}
