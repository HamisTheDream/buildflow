<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = DB::table('payments')
            ->join('organizations', 'payments.organization_id', '=', 'organizations.id')
            ->leftJoin('plans', 'payments.plan_id', '=', 'plans.id')
            ->select(
                'payments.*', 
                'organizations.name as org_name',
                'plans.name as plan_name'
            )
            ->when($request->search, function ($q, $s) {
                $q->where('payments.reference', 'like', "%{$s}%")
                  ->orWhere('organizations.name', 'like', "%{$s}%");
            })
            ->whereNull('payments.deleted_at')
            ->whereNull('organizations.deleted_at')
            ->orderByDesc('payments.created_at')
            ->paginate(50);

        return Inertia::render('Owner/Payments/Index', [
            'payments' => $payments,
            'filters' => $request->only('search'),
        ]);
    }
}
