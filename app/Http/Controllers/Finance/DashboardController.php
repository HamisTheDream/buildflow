<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\Budget;
use App\Models\Finance\Expense;
use App\Models\Finance\Invoice;
use App\Models\Project;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $orgId = $user->current_organization_id;

        // ── Stats ──
        $totalRevenue = Invoice::where('organization_id', $orgId)
            ->where('status', 'paid')
            ->sum('total_amount_cents');

        $totalExpenses = Expense::where('organization_id', $orgId)
            ->whereIn('status', ['approved', 'paid'])
            ->sum('amount_cents');

        $outstandingInvoices = Invoice::where('organization_id', $orgId)
            ->where('status', 'sent')
            ->sum('total_amount_cents');

        $overdueInvoices = Invoice::where('organization_id', $orgId)
            ->whereIn('status', ['sent', 'overdue'])
            ->whereDate('due_date', '<', now())
            ->sum('total_amount_cents');

        $pendingExpenses = Expense::where('organization_id', $orgId)
            ->where('status', 'pending')
            ->count();

        // ── Invoices tab ──
        $invoices = Invoice::where('organization_id', $orgId)
            ->with(['clientUser', 'clientLead', 'project'])
            ->latest()
            ->get();

        // ── Expenses tab ──
        $expenses = Expense::where('organization_id', $orgId)
            ->with(['project', 'reimbursableTo'])
            ->latest('incurred_date')
            ->get();

        // ── Budgets tab ──
        $budgets = Budget::where('organization_id', $orgId)
            ->with('project')
            ->latest()
            ->get()
            ->map(function ($budget) use ($orgId) {
                $spend = Expense::where('organization_id', $orgId)
                    ->where('project_id', $budget->project_id)
                    ->whereBetween('incurred_date', [$budget->start_date, $budget->end_date])
                    ->where('status', '!=', 'rejected')
                    ->sum('amount_cents');

                $budget->spent_amount_cents = $spend;
                $budget->progress = $budget->total_amount_cents > 0
                    ? min(100, round(($spend / $budget->total_amount_cents) * 100))
                    : 0;

                return $budget;
            });

        // ── Overview widgets ──
        $recentInvoices = Invoice::where('organization_id', $orgId)
            ->with(['clientUser', 'clientLead', 'project'])
            ->latest('issue_date')
            ->take(5)
            ->get();

        $recentExpenses = Expense::where('organization_id', $orgId)
            ->with(['project', 'reimbursableTo'])
            ->latest('incurred_date')
            ->take(5)
            ->get();

        // ── Supporting data for modals ──
        $projects = Project::where('organization_id', $orgId)->select('id', 'name')->get();

        return Inertia::render('Finance/Dashboard', [
            'stats' => [
                'revenue' => $totalRevenue,
                'expenses' => $totalExpenses,
                'profit' => $totalRevenue - $totalExpenses,
                'outstanding' => $outstandingInvoices,
                'overdue' => $overdueInvoices,
                'pending_expenses' => $pendingExpenses,
            ],
            'invoices' => $invoices,
            'expenses' => $expenses,
            'budgets' => $budgets,
            'recent_invoices' => $recentInvoices,
            'recent_expenses' => $recentExpenses,
            'projects' => $projects,
        ]);
    }
}
