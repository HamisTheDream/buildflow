<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\HR\Leave;
use App\Models\HR\Payroll;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $orgId = $user->current_organization_id;

        // ── Stats ──
        $totalEmployees = Employee::where('organization_id', $orgId)->where('status', 'active')->count();
        $departmentsCount = Department::where('organization_id', $orgId)->count();

        $totalPayrollYTD = Payroll::where('organization_id', $orgId)
            ->whereYear('run_date', date('Y'))
            ->sum('total_amount_cents');

        $activeLeaves = Leave::where('organization_id', $orgId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->count();

        $pendingLeaves = Leave::where('organization_id', $orgId)
            ->where('status', 'pending')
            ->count();

        // ── Employees (Staff tab) ──
        $employees = Employee::where('organization_id', $orgId)
            ->with(['department', 'user'])
            ->latest()
            ->get();

        // ── Departments tab ──
        $departments = Department::where('organization_id', $orgId)
            ->withCount('employees')
            ->with('manager')
            ->get();

        // ── Payroll tab ──
        $payrolls = Payroll::where('organization_id', $orgId)
            ->latest('run_date')
            ->get();

        // ── Leave tab ──
        $leaves = Leave::where('organization_id', $orgId)
            ->with(['employee', 'approvedBy'])
            ->latest('start_date')
            ->get();

        // ── Overview widgets ──
        $recentHires = Employee::where('organization_id', $orgId)
            ->where('status', 'active')
            ->latest('hire_date')
            ->take(5)
            ->get();

        $upcomingLeaves = Leave::where('organization_id', $orgId)
            ->whereIn('status', ['approved', 'pending'])
            ->whereDate('start_date', '>=', now())
            ->with('employee')
            ->orderBy('start_date')
            ->take(5)
            ->get();

        // ── Supporting data for modals ──
        $activeEmployees = Employee::where('organization_id', $orgId)
            ->where('status', 'active')
            ->select('id', 'first_name', 'last_name')
            ->get();

        $orgUsers = $user->currentOrganization->users()->select('users.id', 'users.name')->get();

        return Inertia::render('HR/Dashboard', [
            'stats' => [
                'employees' => $totalEmployees,
                'departments' => $departmentsCount,
                'payroll_ytd' => $totalPayrollYTD,
                'on_leave' => $activeLeaves,
                'pending_leaves' => $pendingLeaves,
            ],
            'employees' => $employees,
            'departments' => $departments,
            'payrolls' => $payrolls,
            'leaves' => $leaves,
            'recent_hires' => $recentHires,
            'upcoming_leaves' => $upcomingLeaves,
            'active_employees' => $activeEmployees,
            'org_users' => $orgUsers,
        ]);
    }
}
