<?php

namespace App\Http\Controllers\App\Support;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupportTicketController extends Controller
{
    public function create(Request $request)
    {
        return Inertia::render('App/Support/Create', [
            'categories' => ['billing', 'bug', 'feature', 'other'],
            'priorities' => ['low', 'normal', 'high', 'urgent'],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:5000'],
            'category' => ['nullable', 'in:billing,bug,feature,other'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
        ]);

        $user = $request->user();
        $org = \App\Support\CurrentOrg::forUser($user); // Fixed resolution
        if (!$org) {
            return back()->with('error', 'No organization selected.');
        }

        SupportTicket::create([
            'organization_id' => $org->id,
            'created_by_user_id' => $user?->id,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'category' => $data['category'] ?? null,
            'priority' => $data['priority'],
            'status' => 'open',
        ]);

        // Optional: notify support mailbox via SMTP
        // We’ll add this in O5 Support+ later if you want.

        return redirect('/app/support')->with('success', 'Ticket submitted. We’ll get back to you soon.');
    }
}
