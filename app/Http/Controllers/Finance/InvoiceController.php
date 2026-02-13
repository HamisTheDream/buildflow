<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\InvoiceRequest;
use App\Models\Finance\Invoice;
use App\Models\CRM\Lead;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function create()
    {
        $orgId = auth()->user()->current_organization_id;

        return Inertia::render('Finance/Invoices/Create', [
            'clients' => User::whereHas('organizations', function ($q) use ($orgId) {
                $q->where('organizations.id', $orgId);
            })->select('users.id', 'users.name')->get(),
            'leads' => Lead::where('organization_id', $orgId)->select('id', 'first_name', 'last_name')->get(),
            'projects' => Project::where('organization_id', $orgId)->select('id', 'name')->get(),
            'next_number' => 'INV-' . date('Y') . '-' . str_pad(Invoice::where('organization_id', $orgId)->count() + 1, 3, '0', STR_PAD_LEFT),
        ]);
    }

    public function store(InvoiceRequest $request)
    {
        $data = $request->validated();
        $items = $data['items'];
        unset($data['items']);

        // Calculate total
        $totalCents = 0;
        foreach ($items as $item) {
            $totalCents += ($item['quantity'] * ($item['unit_price'] * 100));
        }

        $invoice = Invoice::create([
            'organization_id' => auth()->user()->current_organization_id,
            'number' => 'INV-' . date('Y') . '-' . str_pad(Invoice::where('organization_id', auth()->user()->current_organization_id)->count() + 1, 3, '0', STR_PAD_LEFT),
            'total_amount_cents' => $totalCents,
            'status' => 'draft',
            'meta' => ['items' => $items],
            ...$data,
        ]);

        return redirect()->route('finance.dashboard')->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['clientUser', 'clientLead', 'project', 'payments']);

        return Inertia::render('Finance/Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    public function pdf(Invoice $invoice)
    {
        // Reuse the logic from email method or just duplicate for MVP speed
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('finance.invoices.pdf', compact('invoice'));
        return $pdf->download('Invoice-' . $invoice->number . '.pdf');
    }

    public function email(Invoice $invoice)
    {
        // 1. Validate recipient
        $recipientEmail = null;
        if ($invoice->clientUser) {
            $recipientEmail = $invoice->clientUser->email;
        } elseif ($invoice->clientLead) {
            $recipientEmail = $invoice->clientLead->email;
        }

        if (!$recipientEmail) {
            return back()->with('error', 'No email address found for this client.');
        }

        // 2. Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('finance.invoices.pdf', compact('invoice'));

        // 3. Send Email
        try {
            \Illuminate\Support\Facades\Mail::to($recipientEmail)
                ->send(new \App\Mail\Finance\InvoiceMail($invoice, $pdf->output()));

            $invoice->update(['status' => 'sent']);

            return back()->with('success', "Invoice emailed to {$recipientEmail}");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to email invoice: ' . $e->getMessage());
        }
    }
}
