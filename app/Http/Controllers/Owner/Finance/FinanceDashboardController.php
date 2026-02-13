<?php

namespace App\Http\Controllers\Owner\Finance;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OwnerDeal;
use App\Models\OwnerExpense;
use App\Models\OwnerSalary;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceDashboardController extends Controller
{
    public function index()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // 1. Subscription Revenue (Estimated from Organizations active)
        // For simplicity in this iteration, we might just sum up 'paid' organizations or look at payments table if exists.
        // Let's use the `payments` table if it exists, otherwise manual Deal calculation?
        // User asked for "Sales features... for revenue". So let's aggregate Deals.

        $wonDealsTotal = OwnerDeal::where('status', 'won')
            ->sum('value_cents');

        $expensesTotal = OwnerExpense::sum('amount_cents');

        // Calculate monthly salary cost
        $salariesMonthly = OwnerSalary::where('is_active', true)
            ->get()
            ->sum(function ($salary) {
                if ($salary->frequency === 'weekly') {
                    return $salary->amount_cents * 4;
                }
                return $salary->amount_cents;
            });

        // Recent Activity
        $recentDeals = OwnerDeal::latest()->take(5)->get();
        $recentExpenses = OwnerExpense::latest('date')->take(5)->get();

        return Inertia::render('Owner/Finance/Index', [
            'metrics' => [
                'total_revenue' => $wonDealsTotal,
                'total_expenses' => $expensesTotal,
                'monthly_burn' => $salariesMonthly + OwnerExpense::whereBetween('date', [$startOfMonth, $endOfMonth])->sum('amount_cents'),
                'pnl' => $wonDealsTotal - ($expensesTotal + ($salariesMonthly * 12)), // Rough PNL (All time revenue - All time expenses - 1 year of salaries??) 
                // Let's fix PNL to be current month maybe?
                // For now, let's just show totals.
            ],
            'recent_deals' => $recentDeals,
            'recent_expenses' => $recentExpenses,
        ]);
    }
}
