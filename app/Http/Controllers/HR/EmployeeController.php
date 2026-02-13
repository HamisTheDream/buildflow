<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\EmployeeRequest;
use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    public function create()
    {
        $orgId = auth()->user()->current_organization_id;

        return Inertia::render('HR/Employees/Create', [
            'departments' => Department::where('organization_id', $orgId)->select('id', 'name')->get(),
            'users' => auth()->user()->currentOrganization->users()->select('users.id', 'users.name', 'users.email')->get(),
        ]);
    }

    public function store(EmployeeRequest $request)
    {
        $data = $request->validated();

        $data['salary_amount_cents'] = (int) ($data['salary_amount'] * 100);
        unset($data['salary_amount']);

        Employee::create([
            'organization_id' => auth()->user()->current_organization_id,
            ...$data,
        ]);

        return redirect()->route('hr.dashboard')->with('success', 'Employee added successfully.');
    }

    public function edit(Employee $employee)
    {
        $orgId = auth()->user()->current_organization_id;

        return Inertia::render('HR/Employees/Edit', [
            'employee' => $employee->load(['department', 'user']),
            'departments' => Department::where('organization_id', $orgId)->select('id', 'name')->get(),
            'users' => auth()->user()->currentOrganization->users()->select('users.id', 'users.name', 'users.email')->get(),
        ]);
    }

    public function update(EmployeeRequest $request, Employee $employee)
    {
        $data = $request->validated();

        $data['salary_amount_cents'] = (int) ($data['salary_amount'] * 100);
        unset($data['salary_amount']);

        $employee->update($data);

        return redirect()->route('hr.dashboard')->with('success', 'Employee updated successfully.');
    }
}
