<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\LeaveRequest;
use App\Models\HR\Employee;
use App\Models\HR\Leave;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeaveController extends Controller
{
    public function store(LeaveRequest $request)
    {
        $data = $request->validated();

        Leave::create([
            'organization_id' => auth()->user()->current_organization_id,
            'status' => 'pending',
            ...$data,
        ]);

        return redirect()->back()->with('success', 'Leave request submitted.');
    }

    public function update(Request $request, Leave $leave)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        // Use direct DB update to ensure persistence (debugging Eloquent issue)
        \Illuminate\Support\Facades\DB::table('leaves')
            ->where('id', $leave->id)
            ->update([
                'status' => $validated['status'],
                'approved_by' => auth()->id(),
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Leave request updated.');
    }

    public function destroy(Leave $leave)
    {
        if ($leave->status !== 'pending') {
            return redirect()->back()->with('error', 'Cannot delete processed leave request.');
        }

        $leave->delete();
        return redirect()->back()->with('success', 'Leave request deleted.');
    }
}
