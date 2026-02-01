<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\PaystackService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(private PaystackService $paystack) {}

    public function checkout(Request $request, string $plan)
    {
        $org = $request->user()->currentOrganization;
        abort_unless($org, 403);

        $planModel = Plan::where('key', $plan)->firstOrFail();

        if ($planModel->price_monthly_cents <= 0) {
            // Free plan logic if needed (or just prevent checkout for free)
            return back()->with('error', 'Cannot perform payment for free plan.');
        }

        try {
            $response = $this->paystack->initialize(
                $request->user()->email,
                $planModel->price_monthly_cents,
                [
                    'organization_id' => $org->id,
                    'plan_id' => $planModel->id,
                    'user_id' => $request->user()->id,
                ]
            );

            return inertia()->location($response['authorization_url']);

        } catch (Exception $e) {
            Log::error('Paystack Checkout Error: ' . $e->getMessage());
            return back()->with('error', 'Unable to initialize payment. Please try again.');
        }
    }

    public function callback(Request $request)
    {
        $reference = $request->query('reference');
        if (!$reference) {
            return to_route('app.billing')->with('error', 'No payment reference provided.');
        }

        try {
            $data = $this->paystack->verify($reference);
            
            DB::transaction(function () use ($data, $reference) {
                // 1. Create Payment Record
                $meta = $data['metadata'] ?? [];
                
                DB::table('payments')->insert([
                    'reference' => $reference,
                    'amount' => $data['amount'],
                    'currency' => $data['currency'],
                    'status' => $data['status'],
                    'organization_id' => $meta['organization_id'] ?? 0,
                    'plan_id' => $meta['plan_id'] ?? null,
                    'metadata' => json_encode($data),
                    'gateway_response' => $data['gateway_response'] ?? null,
                    'paid_at' => isset($data['paid_at']) ? parse_date($data['paid_at']) : now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($data['status'] === 'success') {
                    // 2. Activate Plan for Organization
                    $orgId = $meta['organization_id'];
                    $planId = $meta['plan_id'];

                    if ($orgId && $planId) {
                        DB::table('organizations')
                            ->where('id', $orgId)
                            ->update([
                                'plan_id' => $planId,
                                'subscription_status' => 'active',
                                'trial_ends_at' => null, // End trial since paid
                                'updated_at' => now(),
                            ]);
                    }
                }
            });

            return to_route('app.billing')->with('success', 'Payment successful! Your plan has been upgraded.');

        } catch (Exception $e) {
            Log::error('Paystack Callback Error: ' . $e->getMessage());
            return to_route('app.billing')->with('error', 'Payment verification failed.');
        }
    }
}
