<?php

namespace App\Http\Controllers\App\Support;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $org = \App\Support\CurrentOrg::forUser($request->user());
        abort_unless($org, 404);

        $tickets = SupportTicket::query()
            ->where('organization_id', $org->id)
            ->with(['creator:id,name'])
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('App/Support/Index', [
            'tickets' => $tickets,
        ]);
    }

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
        $org = \App\Support\CurrentOrg::forUser($user);
        if (!$org) {
            return back()->with('error', 'No organization selected.');
        }

        $ticket = SupportTicket::create([
            'organization_id' => $org->id,
            'created_by_user_id' => $user?->id,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'category' => $data['category'] ?? null,
            'priority' => $data['priority'],
            'status' => 'open',
        ]);

        return redirect()->route('app.support.show', $ticket->id)->with('success', 'Ticket submitted.');
    }

    public function show(Request $request, SupportTicket $ticket)
    {
        $org = \App\Support\CurrentOrg::forUser($request->user());
        abort_unless($org && $ticket->organization_id === $org->id, 403);

        $ticket->load(['creator:id,name']);

        // Get public conversation messages only (filter out internal admin notes)
        $notes = $ticket->notes()
            ->where('is_public', true)
            ->with(['user:id,name', 'admin:id,name'])
            ->orderBy('created_at')
            ->get()
            ->map(fn($n) => [
                'id' => $n->id,
                'note' => $n->note,
                'is_admin' => $n->admin_id !== null,
                'user_id' => $n->user_id,
                'user' => $n->user ? ['id' => $n->user->id, 'name' => $n->user->name] : null,
                'admin' => $n->admin ? ['name' => $n->admin->name] : null,
                'created_at' => $n->created_at->toDateTimeString(),
            ]);

        return Inertia::render('App/Support/Show', [
            'ticket' => $ticket,
            'notes' => $notes,
        ]);
    }

    public function storeReply(Request $request, SupportTicket $ticket)
    {
        $org = \App\Support\CurrentOrg::forUser($request->user());
        abort_unless($org && $ticket->organization_id === $org->id, 403);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $ticket->notes()->create([
            'user_id' => $request->user()->id,
            'admin_id' => null,
            'note' => $data['message'],
            'is_public' => true,
        ]);

        $ticket->touch(); // Update updated_at for sorting

        return back()->with('success', 'Reply posted.');
    }
}
