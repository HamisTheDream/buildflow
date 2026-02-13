<?php

namespace App\Http\Controllers\Owner\HR;

use App\Http\Controllers\Controller;
use App\Models\PlatformDepartment;
use App\Models\Admin;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OwnerDepartmentsController extends Controller
{
    public function index()
    {
        return Inertia::render('Owner/HR/Departments/Index', [
            'departments' => PlatformDepartment::with('manager')->withCount('members')->get(),
            'potentialManagers' => Admin::active()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:platform_departments,name',
            'manager_id' => 'nullable|exists:admins,id',
        ]);

        PlatformDepartment::create($validated);

        return redirect()->back()->with('success', 'Department created successfully.');
    }

    public function update(Request $request, PlatformDepartment $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:platform_departments,name,' . $department->id,
            'manager_id' => 'nullable|exists:admins,id',
        ]);

        $department->update($validated);

        return redirect()->back()->with('success', 'Department updated successfully.');
    }

    public function destroy(PlatformDepartment $department)
    {
        if ($department->members()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete department with active members.');
        }

        $department->delete();
        return redirect()->back()->with('success', 'Department deleted successfully.');
    }
}
