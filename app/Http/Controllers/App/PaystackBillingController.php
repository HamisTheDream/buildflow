<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Plan;
use App\Services\PaystackClient;
use App\Support\CurrentOrg;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PaystackBillingController extends Controller
{
    public function upgrade(Request $request, PaystackClient $paystack)
    {
        $data = $request->validate([
            'plan_key' => ['required', 'string', 'in:starter,pro'],
        ]);

        $user = $request->user();
        $org = CurrentOrg::forUser($user);
        abort_unless($org, 403);

        $plan = Plan::where('key', $data['plan_key'])->firstOrFail();

        // Amount in kobo for Paystack (NGN smallest unit)
        // We'll treat price_monthly_cents as NGN kobo directly since we updated the seeder
        $amountKobo = (int)$plan->price_monthly_cents;

        $reference = 'bf_' . Str::random(20);

        Payment::create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'gateway' => 'paystack',
            'reference' => $reference,
            'amount_cents' => $amountKobo,
            'currency' => config('paystack.currency'),
            'status' => 'initialized',
            'meta' => [
                'plan_key' => $plan->key,
                'org_id' => $org->id,
            ],
        ]);

        $init = $paystack->initializeTransaction([
            'email' => $user->email,
            'amount' => $amountKobo,
            'currency' => config('paystack.currency'),
            'reference' => $reference,
            'callback_url' => config('paystack.callback_url'),
            'metadata' => [
                'org_id' => $org->id,
                'plan_key' => $plan->key,
                'user_id' => $user->id,
            ],
        ]);

        if (!($init['status'] ?? false)) {
            \Illuminate\Support\Facades\Log::error('Paystack Init Failed', ['response' => $init]);
            return back()->with('error', $init['message'] ?? 'Unable to initialize payment.');
        }

        $authUrl = $init['data']['authorization_url'] ?? null;
        if (!$authUrl) {
            \Illuminate\Support\Facades\Log::error('Paystack No Auth URL', ['response' => $init]);
            return back()->with('error', 'Paystack did not return authorization URL.');
        }

        return Inertia::location($authUrl);
    }

    public function callback(Request $request, PaystackClient $paystack)
    {
        $reference = (string) $request->query('reference');
        abort_unless($reference, 400);

        $payment = Payment::where('reference', $reference)->firstOrFail();

        $verify = $paystack->verifyTransaction($reference);

        $ok = ($verify['status'] ?? false) === true && ($verify['data']['status'] ?? null) === 'success';

        $payment->update([
            'status' => $ok ? 'success' : 'failed',
            'meta' => array_merge($payment->meta ?? [], [
                'verify' => $verify,
            ]),
        ]);

        if (!$ok) {
            return redirect('/app/billing')->with('error', 'Payment verification failed.');
        }

        // Activate subscription for 30 days (manual renewal MVP)
        $org = $payment->organization_id ? \App\Models\Organization::find($payment->organization_id) : null;
        $plan = Plan::find($payment->plan_id);

        if ($org && $plan) {
            $org->plan_id = $plan->id;
            $org->subscription_status = 'active';
            $org->trial_ends_at = null;

            // Extend if already active
            $start = $org->paid_until && now()->lessThanOrEqualTo($org->paid_until)
                ? $org->paid_until
                : now();

            $org->paid_until = $start->copy()->addDays(30);
            $org->save();
        }

        return redirect('/app/billing')->with('success', 'Payment successful. Plan activated.');
    }
}
