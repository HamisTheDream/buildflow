<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OwnerDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Lean metrics (we’ll expand later)
        $today = now()->startOfDay();

        $orgsTotal = Organization::count();
        $orgsActive = Organization::where('subscription_status', 'active')->count();
        $orgsTrial = Organization::where('subscription_status', 'trial')->count();
        $orgsExpired = Organization::where('subscription_status', 'past_due')->count();

        // Payments today (success)
        $paymentsToday = Payment::where('status', 'success')
            ->where('created_at', '>=', $today)
            ->count();

        $revenueTodayKobo = (int) Payment::where('status', 'success')
            ->where('created_at', '>=', $today)
            ->sum('amount_cents'); // stored in kobo in your MVP

        return Inertia::render('Owner/Dashboard', [
            'metrics' => [
                'orgs_total' => $orgsTotal,
                'orgs_active' => $orgsActive,
                'orgs_trial' => $orgsTrial,
                'orgs_expired' => $orgsExpired,
                'payments_today' => $paymentsToday,
                'revenue_today_kobo' => $revenueTodayKobo,
            ],
        ]);
    }
}
