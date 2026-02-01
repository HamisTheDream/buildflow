<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $orgs = Organization::query()
            ->withCount(['users', 'projects'])
            ->with(['plan'])
            ->when($request->search, function ($q, $s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Owner/Tenants/Index', [
            'orgs' => $orgs,
            'filters' => $request->only('search'),
        ]);
    }

    public function show(Organization $organization)
    {
        $organization->load(['plan', 'users']);
        $organization->loadCount(['projects', 'users']);

        $usage = app(\App\Services\UsageService::class)->getUsage($organization);

        return Inertia::render('Owner/Tenants/Show', [
            'organization' => $organization,
            'usage' => $usage,
        ]);
    }
}
