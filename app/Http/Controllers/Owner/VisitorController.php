<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\VisitorLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class VisitorController extends Controller
{
    public function index(Request $request)
    {
        $query = VisitorLog::query()->with('user');

        if ($request->filled('q')) {
            $query->where('ip_address', 'like', '%' . $request->q . '%')
                ->orWhere('url', 'like', '%' . $request->q . '%')
                ->orWhere('country', 'like', '%' . $request->q . '%')
                ->orWhere('city', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('country')) {
            $query->where('country_code', $request->country);
        }

        $logs = $query->latest('visit_time')->paginate(20)->withQueryString();

        // Stats
        $stats = [
            'total_visits' => VisitorLog::count(),
            'unique_visitors' => VisitorLog::distinct('ip_address')->count(),
            'top_countries' => VisitorLog::select('country', 'country_code', DB::raw('count(*) as total'))
                ->whereNotNull('country')
                ->groupBy('country', 'country_code')
                ->orderByDesc('total')
                ->limit(5)
                ->get(),
            'recent_visits' => VisitorLog::where('visit_time', '>=', now()->subDay())->count(),
        ];

        return Inertia::render('Owner/Visitors/Index', [
            'logs' => $logs,
            'stats' => $stats,
            'filters' => $request->only(['q', 'country']),
        ]);
    }
}
