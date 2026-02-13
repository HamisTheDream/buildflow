<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Finance\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function show(Request $request, Invoice $invoice)
    {
        if (!$request->hasValidSignature()) {
            abort(403);
        }

        $invoice->load(['organization', 'clientUser', 'clientLead', 'project', 'payments']);

        return Inertia::render('Public/Invoice/Show', [
            'invoice' => $invoice,
            'organization' => [
                'name' => $invoice->organization->brand_name ?? $invoice->organization->name,
                'email' => $invoice->organization->billingEmail(),
                'logo' => $invoice->organization->brand_logo_path ? asset('storage/' . $invoice->organization->brand_logo_path) : null,
                'color' => $invoice->organization->brand_color,
            ],
            'payUrl' => \Illuminate\Support\Facades\URL::signedRoute('public.invoice.pay', ['invoice' => $invoice->id]),
        ]);
    }
}
