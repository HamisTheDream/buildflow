<?php

namespace App\Http\Controllers\Owner\Admins;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\AdminAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class OwnerAdminsController extends Controller
{
    public function index(Request $request)
    {
        $q = (string) $request->query('q', '');

        $admins = Admin::query()
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            })
            ->orderByDesc('is_super')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString()
            ->through(fn($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'email' => $a->email,
                'is_super' => (bool)$a->is_super,
                'is_active' => (bool)$a->is_active,
                'created_at' => $a->created_at->toDateTimeString(),
            ]);

        $me = Auth::guard('owner')->user();

        return Inertia::render('Owner/Admins/Index', [
            'filters' => ['q' => $q],
            'admins' => $admins,
            'me' => [
                'id' => $me->id,
                'is_super' => (bool)$me->is_super,
            ],
        ]);
    }

    public function create()
    {
        $me = Auth::guard('owner')->user();
        if (!$me->is_super) return redirect('/owner/admins')->with('error', 'Super admin required.');

        return Inertia::render('Owner/Admins/Create');
    }

    public function store(Request $request, AdminAudit $audit)
    {
        $me = Auth::guard('owner')->user();
        if (!$me->is_super) return back()->with('error', 'Super admin required.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:120', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:10', 'max:120'],
            'is_super' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'audit_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $admin = Admin::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => Hash::make($data['password']),
            'is_super' => (bool)$data['is_super'],
            'is_active' => (bool)$data['is_active'],
        ]);

        $audit->log(
            $me->id,
            'admin.create',
            $admin,
            [],
            ['id' => $admin->id, 'email' => $admin->email, 'is_super' => $admin->is_super, 'is_active' => $admin->is_active],
            $data['audit_reason'] ?? null,
            $request->ip(),
            substr((string)$request->userAgent(), 0, 512)
        );

        return redirect('/owner/admins')->with('success', 'Admin created.');
    }

    public function toggleSuper(Request $request, Admin $admin, AdminAudit $audit)
    {
        $me = Auth::guard('owner')->user();
        if (!$me->is_super) return back()->with('error', 'Super admin required.');

        if ($admin->id === $me->id) {
            return back()->with('error', 'You cannot change your own super status.');
        }

        $data = $request->validate([
            'audit_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $before = $admin->only(['is_super']);
        $admin->is_super = !$admin->is_super;
        $admin->save();
        $after = $admin->only(['is_super']);

        $audit->log(
            $me->id,
            'admin.toggle_super',
            $admin,
            $before,
            $after,
            $data['audit_reason'] ?? null,
            $request->ip(),
            substr((string)$request->userAgent(), 0, 512)
        );

        return back()->with('success', 'Super status updated.');
    }

    public function toggleActive(Request $request, Admin $admin, AdminAudit $audit)
    {
        $me = Auth::guard('owner')->user();
        if (!$me->is_super) return back()->with('error', 'Super admin required.');

        if ($admin->id === $me->id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $data = $request->validate([
            'audit_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $before = $admin->only(['is_active']);
        $admin->is_active = !$admin->is_active;
        $admin->save();
        $after = $admin->only(['is_active']);

        $audit->log(
            $me->id,
            'admin.toggle_active',
            $admin,
            $before,
            $after,
            $data['audit_reason'] ?? null,
            $request->ip(),
            substr((string)$request->userAgent(), 0, 512)
        );

        return back()->with('success', 'Admin active status updated.');
    }
}
