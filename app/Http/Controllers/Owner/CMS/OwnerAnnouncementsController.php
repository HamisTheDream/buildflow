<?php

namespace App\Http\Controllers\Owner\CMS;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Organization;
use App\Models\Plan;
use App\Services\AdminAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OwnerAnnouncementsController extends Controller
{
    public function index(Request $request)
    {
        $q = (string) $request->query('q', '');
        $active = (string) $request->query('active', ''); // '', '1', '0'

        $rows = Announcement::query()
            ->with(['plan:id,key,name', 'organization:id,name'])
            ->when($q, function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                      ->orWhere('body', 'like', "%{$q}%");
            })
            ->when($active !== '', function ($query) use ($active) {
                $query->where('is_active', $active === '1');
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'tone' => $a->tone,
                'is_active' => (bool)$a->is_active,
                'is_global' => (bool)$a->is_global,
                'plan' => $a->plan ? ['key' => $a->plan->key, 'name' => $a->plan->name] : null,
                'organization' => $a->organization ? ['id' => $a->organization->id, 'name' => $a->organization->name] : null,
                'starts_at' => $a->starts_at?->toDateTimeString(),
                'ends_at' => $a->ends_at?->toDateTimeString(),
                'created_at' => $a->created_at->toDateTimeString(),
            ]);

        return Inertia::render('Owner/Announcements/Index', [
            'filters' => [
                'q' => $q,
                'active' => $active,
            ],
            'announcements' => $rows,
        ]);
    }

    public function create()
    {
        $plans = Plan::orderBy('id')->get(['id','key','name']);
        $orgs = Organization::orderBy('name')->limit(200)->get(['id','name']); // keep lean

        return Inertia::render('Owner/Announcements/Create', [
            'plans' => $plans,
            'organizations' => $orgs,
        ]);
    }

    public function store(Request $request, AdminAudit $audit)
    {
        $admin = Auth::guard('owner')->user();

        $data = $this->validateAnnouncement($request);

        // Targeting logic:
        // - global: no org_id, no plan_id
        // - plan-targeted: plan_id set, org_id null, is_global false
        // - org-targeted: org_id set, plan_id null, is_global false
        [$data['is_global'], $data['plan_id'], $data['organization_id']] = $this->normalizeTarget($data);

        $data['created_by_admin_id'] = $admin->id;

        $a = Announcement::create($data);

        $audit->log(
            $admin->id,
            'announcement.create',
            $a,
            [],
            $a->toArray(),
            $request->input('audit_reason'),
            $request->ip(),
            substr((string)$request->userAgent(), 0, 512)
        );

        return redirect('/owner/announcements')->with('success', 'Announcement created.');
    }

    public function edit(Announcement $announcement)
    {
        $plans = Plan::orderBy('id')->get(['id','key','name']);
        $orgs = Organization::orderBy('name')->limit(200)->get(['id','name']);

        return Inertia::render('Owner/Announcements/Edit', [
            'plans' => $plans,
            'organizations' => $orgs,
            'announcement' => [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'body' => $announcement->body,
                'tone' => $announcement->tone,
                'cta_text' => $announcement->cta_text,
                'cta_url' => $announcement->cta_url,
                'is_active' => (bool)$announcement->is_active,
                'is_global' => (bool)$announcement->is_global,
                'plan_id' => $announcement->plan_id,
                'organization_id' => $announcement->organization_id,
                'starts_at' => $announcement->starts_at?->format('Y-m-d\TH:i'),
                'ends_at' => $announcement->ends_at?->format('Y-m-d\TH:i'),
            ],
        ]);
    }

    public function update(Request $request, Announcement $announcement, AdminAudit $audit)
    {
        $admin = Auth::guard('owner')->user();

        $before = $announcement->toArray();

        $data = $this->validateAnnouncement($request);
        [$data['is_global'], $data['plan_id'], $data['organization_id']] = $this->normalizeTarget($data);

        $announcement->update($data);

        $audit->log(
            $admin->id,
            'announcement.update',
            $announcement,
            $before,
            $announcement->toArray(),
            $request->input('audit_reason'),
            $request->ip(),
            substr((string)$request->userAgent(), 0, 512)
        );

        return redirect('/owner/announcements')->with('success', 'Announcement updated.');
    }

    public function destroy(Request $request, Announcement $announcement, AdminAudit $audit)
    {
        $admin = Auth::guard('owner')->user();

        // Optional: only super admin can delete (safe default)
        if (!$admin->is_super) {
            return back()->with('error', 'Super admin required to delete announcements.');
        }

        $before = $announcement->toArray();

        $announcement->delete();

        $audit->log(
            $admin->id,
            'announcement.delete',
            null,
            $before,
            [],
            $request->input('audit_reason'),
            $request->ip(),
            substr((string)$request->userAgent(), 0, 512)
        );

        return back()->with('success', 'Announcement deleted.');
    }

    private function validateAnnouncement(Request $request): array
    {
        return $request->validate([
            'title' => ['required','string','max:120'],
            'body' => ['nullable','string','max:2000'],
            'tone' => ['required','in:info,success,warning,danger'],
            'cta_text' => ['nullable','string','max:40'],
            'cta_url' => ['nullable','url','max:255'],

            // targeting
            'target' => ['required','in:global,plan,organization'],
            'plan_id' => ['nullable','integer','exists:plans,id'],
            'organization_id' => ['nullable','integer','exists:organizations,id'],

            // timing
            'is_active' => ['required','boolean'],
            'starts_at' => ['nullable','date'],
            'ends_at' => ['nullable','date','after:starts_at'],

            // audit
            'audit_reason' => ['nullable','string','max:255'],
        ]);
    }

    private function normalizeTarget(array $data): array
    {
        $target = $data['target'] ?? 'global';

        if ($target === 'global') {
            return [true, null, null];
        }

        if ($target === 'plan') {
            return [false, (int)($data['plan_id'] ?? 0) ?: null, null];
        }

        // organization
        return [false, null, (int)($data['organization_id'] ?? 0) ?: null];
    }
}
