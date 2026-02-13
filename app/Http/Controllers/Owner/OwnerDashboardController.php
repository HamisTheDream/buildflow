<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\User;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class OwnerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        // Organization metrics
        $orgsTotal = Organization::count();
        $orgsActive = Organization::where('subscription_status', 'active')->count();
        $orgsTrial = Organization::where('subscription_status', 'trial')->count();
        $orgsExpired = Organization::whereIn('subscription_status', ['past_due', 'expired'])->count();
        $orgsThisMonth = Organization::where('created_at', '>=', $thisMonth)->count();

        // User metrics
        $usersTotal = User::count();
        $usersThisMonth = User::where('created_at', '>=', $thisMonth)->count();

        // Support ticket metrics
        $ticketsOpen = SupportTicket::whereIn('status', ['open', 'pending'])->count();
        $ticketsTotal = SupportTicket::count();

        // Revenue metrics
        $paymentsToday = Payment::where('status', 'success')
            ->where('created_at', '>=', $today)
            ->count();

        $revenueTodayKobo = (int) Payment::where('status', 'success')
            ->where('created_at', '>=', $today)
            ->sum('amount_cents');

        $revenueThisMonthKobo = (int) Payment::where('status', 'success')
            ->where('created_at', '>=', $thisMonth)
            ->sum('amount_cents');

        // MRR calculation (sum of active subscriptions' plan prices)
        $mrr = Organization::where('subscription_status', 'active')
            ->whereNotNull('plan_id')
            ->with('plan')
            ->get()
            ->sum(fn($org) => $org->plan?->price_monthly_kobo ?? 0);

        // Churn & LTV (Approximated)
        // Churn = (Expired / (Active + Expired)) * 100
        $totalPaying = $orgsActive + $orgsExpired;
        $churnRate = $totalPaying > 0 ? ($orgsExpired / $totalPaying) * 100 : 0;

        // ARPU = MRR / Active Orgs
        $arpu = $orgsActive > 0 ? ($mrr / $orgsActive) : 0;

        // LTV = ARPU / Churn (decimal)
        // If churn is 5%, LTV = ARPU / 0.05 = ARPU * 20
        $churnDecimal = $churnRate / 100;
        $ltvKobo = ($churnDecimal > 0) ? ($arpu / $churnDecimal) : 0;

        // Revenue chart data (last 6 months)
        $revenueChart = collect(range(5, 0))->map(function ($monthsAgo) {
            $start = now()->subMonths($monthsAgo)->startOfMonth();
            $end = now()->subMonths($monthsAgo)->endOfMonth();
            $revenue = Payment::where('status', 'success')
                ->whereBetween('created_at', [$start, $end])
                ->sum('amount_cents');
            return [
                'month' => $start->format('M'),
                'revenue' => (int) $revenue,
            ];
        })->values()->toArray();

        // Signups chart data (last 6 months)
        $signupsChart = collect(range(5, 0))->map(function ($monthsAgo) {
            $start = now()->subMonths($monthsAgo)->startOfMonth();
            $end = now()->subMonths($monthsAgo)->endOfMonth();
            $count = Organization::whereBetween('created_at', [$start, $end])->count();
            return [
                'month' => $start->format('M'),
                'signups' => $count,
            ];
        })->values()->toArray();

        // Recent activity (last 5 tickets)
        $recentTickets = SupportTicket::with(['creator:id,name', 'organization:id,name'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'organization_id', 'created_by_user_id', 'subject', 'status', 'created_at']);

        // Visitor metrics
        $visitorsTotal = \App\Models\VisitorLog::count();
        $visitorsToday = \App\Models\VisitorLog::where('visit_time', '>=', $today)->count();
        $uniqueVisitors = \App\Models\VisitorLog::distinct('ip_address')->count();

        // Visitors chart data (last 6 months)
        $visitorsChart = collect(range(5, 0))->map(function ($monthsAgo) {
            $start = now()->subMonths($monthsAgo)->startOfMonth();
            $end = now()->subMonths($monthsAgo)->endOfMonth();
            $count = \App\Models\VisitorLog::whereBetween('visit_time', [$start, $end])->count();
            return [
                'month' => $start->format('M'),
                'visitors' => $count,
            ];
        })->values()->toArray();

        return Inertia::render('Owner/Dashboard', [
            'metrics' => [
                'orgs_total' => $orgsTotal,
                'orgs_active' => $orgsActive,
                'orgs_trial' => $orgsTrial,
                'orgs_expired' => $orgsExpired,
                'orgs_this_month' => $orgsThisMonth,
                'users_total' => $usersTotal,
                'users_this_month' => $usersThisMonth,
                'tickets_open' => $ticketsOpen,
                'tickets_total' => $ticketsTotal,
                'payments_today' => $paymentsToday,
                'revenue_today_kobo' => $revenueTodayKobo,
                'revenue_month_kobo' => $revenueThisMonthKobo,
                'revenue_today_kobo' => $revenueTodayKobo, // Duplicate key in original, keeping it safe
                'mrr_kobo' => $mrr,
                'churn_rate' => $churnRate,
                'ltv_kobo' => $ltvKobo,
                'visitors_total' => $visitorsTotal,
                'visitors_today' => $visitorsToday,
                'visitors_unique' => $uniqueVisitors,
            ],
            'revenueChart' => $revenueChart,
            'signupsChart' => $signupsChart,
            'visitorsChart' => $visitorsChart,
            'recentTickets' => $recentTickets,
        ]);
    }
}
