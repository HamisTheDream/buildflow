<?php

namespace App\Http\Controllers\Owner\HR;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\PlatformDepartment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class OwnerEmployeesController extends Controller
{
    public function index(Request $request)
    {
        $query = Admin::query()->with('department');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('role')) {
            $query->where('role_type', $request->role);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        return Inertia::render('Owner/HR/Employees/Index', [
            'employees' => $query->latest()->paginate(10)->withQueryString(),
            'departments' => PlatformDepartment::all(),
            'filters' => $request->only(['search', 'role', 'department_id']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Owner/HR/Employees/Create', [
            'departments' => PlatformDepartment::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:8',
            'role_type' => 'required|string',
            'department_id' => 'nullable|exists:platform_departments,id',
            'job_title' => 'nullable|string|max:255',
            'status' => 'required|in:active,suspended',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['is_super'] = false; // By default not super admin unless explicitly set via another flow

        Admin::create($validated);

        return redirect()->route('owner.hr.employees.index')->with('success', 'Employee created successfully.');
    }

    public function edit(Admin $employee)
    {
        return Inertia::render('Owner/HR/Employees/Edit', [
            'employee' => $employee,
            'departments' => PlatformDepartment::all(),
        ]);
    }

    public function update(Request $request, Admin $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('admins')->ignore($employee->id)],
            'password' => 'nullable|string|min:8',
            'role_type' => 'required|string',
            'department_id' => 'nullable|exists:platform_departments,id',
            'job_title' => 'nullable|string|max:255',
            'status' => 'required|in:active,suspended',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $employee->update($validated);

        return redirect()->back()->with('success', 'Employee updated successfully.');
    }

    public function destroy(Admin $employee)
    {
        if ($employee->is_super) {
            return redirect()->back()->with('error', 'Cannot delete a Super Admin from here.');
        }

        $employee->delete();
        return redirect()->route('owner.hr.employees.index')->with('success', 'Employee deleted successfully.');
    }
}
