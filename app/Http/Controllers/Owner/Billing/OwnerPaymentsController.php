<?php

namespace App\Http\Controllers\Owner\Billing;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\PaymentNote;
use App\Services\AdminAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class OwnerPaymentsController extends Controller
{
    public function index(Request $request)
    {
        $q = (string) $request->query('q', '');
        $status = (string) $request->query('status', '');

        $payments = Payment::query()
            ->with(['organization:id,name'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($nested) use ($q) {
                    $nested->where('reference', 'like', "%{$q}%")
                        ->orWhere('gateway_reference', 'like', "%{$q}%");
                });
            })
            ->when($status, fn($query) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString()
            ->through(fn($p) => [
                'id' => $p->id,
                'organization' => $p->organization ? ['id' => $p->organization->id, 'name' => $p->organization->name] : null,
                'reference' => $p->reference,
                'status' => $p->status,
                'amount_kobo' => (int) $p->amount_cents,
                'currency' => $p->currency,
                'created_at' => $p->created_at->toDateTimeString(),
            ]);

        return Inertia::render('Owner/Billing/Payments/Index', [
            'filters' => [
                'q' => $q,
                'status' => $status,
            ],
            'payments' => $payments,
        ]);
    }

    public function show(Request $request, Payment $payment)
    {
        $payment->load(['organization:id,name']);

        $notes = PaymentNote::where('payment_id', $payment->id)
            ->with('admin:id,name,email')
            ->orderByDesc('id')
            ->get()
            ->map(fn($n) => [
                'id' => $n->id,
                'note' => $n->note,
                'admin' => $n->admin ? ['name' => $n->admin->name, 'email' => $n->admin->email] : null,
                'created_at' => $n->created_at->toDateTimeString(),
            ])->values();

        return Inertia::render('Owner/Billing/Payments/Show', [
            'payment' => [
                'id' => $payment->id,
                'organization' => $payment->organization ? ['id' => $payment->organization->id, 'name' => $payment->organization->name] : null,
                'reference' => $payment->reference,
                'gateway_reference' => $payment->gateway_reference,
                'status' => $payment->status,
                'amount_kobo' => (int) $payment->amount_cents,
                'currency' => $payment->currency,
                'raw_payload' => $payment->raw_payload ?? null,
                'created_at' => $payment->created_at->toDateTimeString(),
            ],
            'notes' => $notes,
        ]);
    }

    /**
     * Owner tool: verify a Paystack reference again and reconcile status.
     * This is for stuck payments / webhook delays.
     */
    public function verifyReference(Request $request, AdminAudit $audit)
    {
        $admin = Auth::guard('owner')->user();
        if (!$admin->is_super) {
            return back()->with('error', 'Super admin required.');
        }

        $data = $request->validate([
            'reference' => ['required','string','max:120'],
            'reason' => ['nullable','string','max:255'],
        ]);

        // call Paystack verify endpoint
        $secret = config('services.paystack.secret_key');
        if (!$secret) {
            return back()->with('error', 'PAYSTACK_SECRET_KEY missing in config/services.php');
        }

        $resp = Http::withToken($secret)->get("https://api.paystack.co/transaction/verify/{$data['reference']}");

        if (!$resp->ok()) {
            return back()->with('error', 'Verify failed: '.$resp->status());
        }

        $body = $resp->json();
        $status = data_get($body, 'data.status'); // "success", "failed", "abandoned"
        $amount = (int) data_get($body, 'data.amount', 0);
        $currency = (string) data_get($body, 'data.currency', 'NGN');

        $payment = Payment::where('reference', $data['reference'])->first();
        if (!$payment) {
            return back()->with('error', 'Payment record not found in database for that reference.');
        }

        $before = $payment->only(['status','amount_cents','currency']);

        // Map paystack statuses
        $mapped = $status === 'success' ? 'success' : ($status === 'failed' ? 'failed' : 'pending');

        $payment->status = $mapped;
        $payment->amount_cents = $amount ?: $payment->amount_cents;
        $payment->currency = $currency ?: $payment->currency;
        $payment->raw_payload = $body;
        $payment->save();

        $after = $payment->only(['status','amount_cents','currency']);

        $audit->log(
            $admin->id,
            'payment.verify_reference',
            $payment,
            $before,
            $after,
            $data['reason'] ?? null,
            $request->ip(),
            substr((string)$request->userAgent(), 0, 512)
        );

        return back()->with('success', "Verified: Paystack status={$status} mapped={$mapped}");
    }

    public function addNote(Request $request, Payment $payment)
    {
        $admin = Auth::guard('owner')->user();

        $data = $request->validate([
            'note' => ['required','string','max:500'],
        ]);

        PaymentNote::create([
            'payment_id' => $payment->id,
            'admin_id' => $admin->id,
            'note' => $data['note'],
        ]);

        return back()->with('success', 'Note added.');
    }
}
