<?php

namespace App\Http\Controllers\Owner\CRM;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizationNoteController extends Controller
{
    public function store(Request $request, Organization $organization)
    {
        $data = $request->validate([
            'content' => ['required', 'string'],
            'type' => ['required', 'in:note,call,email,meeting'],
        ]);

        $note = $organization->notes()->create([
            'admin_id' => Auth::guard('owner')->id(),
            'content' => $data['content'],
            'type' => $data['type'],
        ]);

        return back()->with('success', 'Note added successfully.');
    }

    public function destroy(OrganizationNote $note)
    {
        // Check policy
        if (!Auth::guard('owner')->user()->can('delete', $note)) {
            abort(403, 'You are not authorized to delete this note.');
        }

        $note->delete();

        return back()->with('success', 'Note deleted successfully.');
    }
}
