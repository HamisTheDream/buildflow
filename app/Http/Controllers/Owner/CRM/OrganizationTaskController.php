<?php

namespace App\Http\Controllers\Owner\CRM;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizationTaskController extends Controller
{
    public function store(Request $request, Organization $organization)
    {
        $data = $request->validate([
            'content' => ['required', 'string', 'max:255'],
            'due_at' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:admins,id'],
        ]);

        $task = $organization->crmTasks()->create([
            'content' => $data['content'],
            'due_at' => $data['due_at'],
            'assigned_to' => $data['assigned_to'] ?? Auth::guard('owner')->id(),
            'created_by' => Auth::guard('owner')->id(),
        ]);

        // Notify assignee if it's not the creator
        if ($task->assigned_to && $task->assigned_to !== $task->created_by) {
            $task->load(['organization', 'creator', 'assignee']);
            $task->assignee->notify(new \App\Notifications\CRM\TaskAssigned($task));
        }

        return back()->with('success', 'Task created successfully.');
    }

    public function update(Request $request, OrganizationTask $task)
    {
        $data = $request->validate([
            'is_completed' => ['nullable', 'boolean'],
            'content' => ['nullable', 'string', 'max:255'],
            'due_at' => ['nullable', 'date'],
        ]);

        if (isset($data['is_completed'])) {
            $task->completed_at = $data['is_completed'] ? now() : null;
        }

        if (isset($data['content'])) {
            $task->content = $data['content'];
        }

        if (isset($data['due_at'])) {
            $task->due_at = $data['due_at'];
        }

        $task->save();

        return back()->with('success', 'Task updated.');
    }

    public function destroy(OrganizationTask $task)
    {
        if (!Auth::guard('owner')->user()->can('delete', $task)) {
            abort(403, 'You are not authorized to delete this task.');
        }

        $task->delete();
        return back()->with('success', 'Task deleted.');
    }
}
