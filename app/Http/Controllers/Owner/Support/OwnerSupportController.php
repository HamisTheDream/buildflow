<?php

namespace App\Http\Controllers\Owner\Support;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketNote;
use App\Notifications\TicketUpdated;
use App\Services\AdminAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OwnerSupportController extends Controller
{
    public function index(Request $request)
    {
        $q = (string) $request->query('q', '');
        $status = (string) $request->query('status', '');
        $priority = (string) $request->query('priority', '');

        $tickets = SupportTicket::query()
            ->with(['organization:id,name', 'creator:id,name,email'])
            ->when($q, function ($query) use ($q) {
                $query->where('subject', 'like', "%{$q}%")
                    ->orWhereHas('organization', fn($o) => $o->where('name', 'like', "%{$q}%"));
            })
            ->when($status, fn($query) => $query->where('status', $status))
            ->when($priority, fn($query) => $query->where('priority', $priority))
            ->orderByRaw("CASE status
                WHEN 'open' THEN 1
                WHEN 'pending' THEN 2
                WHEN 'resolved' THEN 3
                WHEN 'closed' THEN 4
                ELSE 5 END")
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn($t) => [
                'id' => $t->id,
                'subject' => $t->subject,
                'status' => $t->status,
                'priority' => $t->priority,
                'category' => $t->category,
                'organization' => $t->organization ? ['id' => $t->organization->id, 'name' => $t->organization->name] : null,
                'creator' => $t->creator ? ['name' => $t->creator->name, 'email' => $t->creator->email] : null,
                'created_at' => $t->created_at->toDateTimeString(),
            ]);

        return Inertia::render('Owner/Support/Index', [
            'filters' => compact('q', 'status', 'priority'),
            'tickets' => $tickets,
            'statuses' => ['open', 'pending', 'resolved', 'closed'],
            'priorities' => ['low', 'normal', 'high', 'urgent'],
        ]);
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load(['organization:id,name', 'creator:id,name,email']);

        // Get conversation messages (public notes from both users and admins)
        $conversation = $ticket->notes()
            ->where('is_public', true)
            ->with(['admin:id,name,email', 'user:id,name,email'])
            ->orderBy('created_at')
            ->get()
            ->map(fn($n) => [
                'id' => $n->id,
                'note' => $n->note,
                'is_admin' => $n->admin_id !== null,
                'author' => $n->admin_id
                    ? ['name' => $n->admin?->name ?? 'Admin', 'email' => $n->admin?->email]
                    : ['name' => $n->user?->name ?? 'User', 'email' => $n->user?->email],
                'created_at' => $n->created_at->toDateTimeString(),
            ])->values();

        // Get internal notes (admin-only, not visible to customers)
        $internalNotes = $ticket->notes()
            ->where('is_public', false)
            ->with('admin:id,name,email')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($n) => [
                'id' => $n->id,
                'note' => $n->note,
                'admin' => $n->admin ? ['name' => $n->admin->name, 'email' => $n->admin->email] : null,
                'created_at' => $n->created_at->toDateTimeString(),
            ])->values();

        return Inertia::render('Owner/Support/Show', [
            'ticket' => [
                'id' => $ticket->id,
                'subject' => $ticket->subject,
                'message' => $ticket->message,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'category' => $ticket->category,
                'organization' => $ticket->organization ? ['id' => $ticket->organization->id, 'name' => $ticket->organization->name] : null,
                'creator' => $ticket->creator ? ['name' => $ticket->creator->name, 'email' => $ticket->creator->email] : null,
                'created_at' => $ticket->created_at->toDateTimeString(),
            ],
            'conversation' => $conversation,
            'internalNotes' => $internalNotes,
            'statuses' => ['open', 'pending', 'resolved', 'closed'],
            'priorities' => ['low', 'normal', 'high', 'urgent'],
        ]);
    }

    public function update(Request $request, SupportTicket $ticket, AdminAudit $audit)
    {
        $admin = Auth::guard('owner')->user();

        $data = $request->validate([
            'status' => ['required', 'in:open,pending,resolved,closed'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'audit_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $before = $ticket->only(['status', 'priority']);

        $ticket->update([
            'status' => $data['status'],
            'priority' => $data['priority'],
        ]);

        $after = $ticket->only(['status', 'priority']);

        $audit->log(
            $admin->id,
            'support.ticket.update',
            $ticket,
            $before,
            $after,
            $data['audit_reason'] ?? null,
            $request->ip(),
            substr((string)$request->userAgent(), 0, 512)
        );

        if ($ticket->creator) {
            $ticket->creator->notify(new TicketUpdated($ticket, 'status_updated'));
        }

        return back()->with('success', 'Ticket updated.');
    }

    /**
     * Send a public reply visible to the customer
     */
    public function storeReply(Request $request, SupportTicket $ticket, AdminAudit $audit)
    {
        $admin = Auth::guard('owner')->user();

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $note = SupportTicketNote::create([
            'support_ticket_id' => $ticket->id,
            'admin_id' => $admin->id,
            'user_id' => null,
            'note' => $data['message'],
            'is_public' => true,
        ]);

        $ticket->touch(); // Update updated_at for sorting

        $audit->log(
            $admin->id,
            'support.ticket.reply',
            $ticket,
            [],
            ['note_id' => $note->id],
            null,
            $request->ip(),
            substr((string)$request->userAgent(), 0, 512)
        );

        if ($ticket->creator) {
            $ticket->creator->notify(new TicketUpdated($ticket, 'replied', $data['message']));
        }

        return back()->with('success', 'Reply sent.');
    }

    /**
     * Add an internal note (only visible to admins)
     */
    public function addNote(Request $request, SupportTicket $ticket, AdminAudit $audit)
    {
        $admin = Auth::guard('owner')->user();

        $data = $request->validate([
            'note' => ['required', 'string', 'max:5000'],
            'audit_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $note = SupportTicketNote::create([
            'support_ticket_id' => $ticket->id,
            'admin_id' => $admin->id,
            'user_id' => null,
            'note' => $data['note'],
            'is_public' => false,
        ]);

        $audit->log(
            $admin->id,
            'support.ticket.note',
            $ticket,
            [],
            ['note_id' => $note->id],
            $data['audit_reason'] ?? null,
            $request->ip(),
            substr((string)$request->userAgent(), 0, 512)
        );

        return back()->with('success', 'Note added.');
    }
}
