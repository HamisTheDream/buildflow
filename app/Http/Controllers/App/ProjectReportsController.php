<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Mail\ProjectReportMail;
use App\Models\Project;
use App\Models\ProjectReport;
use App\Models\Attachment;
use App\Models\ProjectMedia;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use App\Services\ActivityLogger;

class ProjectReportsController extends Controller
{
    public function index(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $reports = ProjectReport::query()
            ->where('project_id', $project->id)
            ->with('generator:id,name,email')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $reports->getCollection()->transform(fn ($r) => [
            'id' => $r->id,
            'from_date' => $r->from_date->toDateString(),
            'to_date' => $r->to_date->toDateString(),
            'title' => $r->title,
            'type' => $r->type,
            'share_token' => $r->share_token,
            'share_expires_at' => $r->share_expires_at?->toDateTimeString(),
            'share_password_protected' => !empty($r->share_password_hash),
            'created_at' => $r->created_at->toDateTimeString(),
            'summary' => $r->summary ?? null,
            'can_delete' => $request->user()->can('delete', [$r, $project]),
            'generator' => $r->generator ? [
                'id' => $r->generator->id,
                'name' => $r->generator->name,
                'email' => $r->generator->email,
            ] : null,
        ]);

        return Inertia::render('App/Projects/Reports', [
            'project' => $project->only(['id','name','status']),
            'reports' => $reports,
        ]);
    }

    public function generate(Request $request, Project $project, ReportService $reports, ActivityLogger $activity)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        Gate::authorize('generate', [ProjectReport::class, $project]);

        $data = $request->validate([
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'title' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:daily,summary,cost'],
            'share_expires_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'share_password' => ['nullable', 'string', 'min:4', 'max:100'],
            'options' => ['nullable', 'array'],
            'options.include_today_logs' => ['nullable', 'boolean'],
            'options.include_logs' => ['nullable', 'boolean'],
            'options.include_tasks' => ['nullable', 'boolean'],
            'options.include_issues' => ['nullable', 'boolean'],
            'options.include_costs' => ['nullable', 'boolean'],
            'send_email' => ['nullable', 'boolean'],
            'email_to' => ['nullable', 'email'],
        ]);

        $project->load('organization');
        $gate = app(\App\Services\UsageService::class)->withinLimits($project->organization);

        if (!empty($data['share_password']) && !$gate['can']['password_protect_reports']) {
            return back()->with('error', 'Password-protected share links require Starter plan or above.');
        }

        $options = $data['options'] ?? [
            'include_today_logs' => true,
            'include_logs' => true,
            'include_tasks' => true,
            'include_issues' => true,
            'include_costs' => true,
        ];

        $from = $data['from_date'];
        $to = $data['to_date'];

        $payload = $reports->buildPayload($project, $from, $to, $options);

        $title = $data['title'] ?: "{$project->name} Report ({$from} to {$to})";

        $pdfBytes = $reports->renderPdf([
            'project' => $project,
            'from' => $from,
            'to' => $to,
            'title' => $title,
            'subtitle' => Str::title($data['type']) . ' Report',
            'generatedAt' => now()->toDateTimeString(),
            'generatedBy' => $request->user()->name,
            'options' => $options,
            ...$payload,
        ]);

        $token = Str::random(40);
        $fileName = "projects/{$project->id}/reports/" . now()->format('Ymd_His') . "_" . Str::slug($project->name) . ".pdf";

        Storage::disk('public')->put($fileName, $pdfBytes);

        $expiresAt = !empty($data['share_expires_days'])
            ? now()->addDays((int)$data['share_expires_days'])
            : null;

        $pwdHash = !empty($data['share_password'])
            ? \Illuminate\Support\Facades\Hash::make($data['share_password'])
            : null;

        $report = ProjectReport::create([
            'project_id' => $project->id,
            'generated_by' => $request->user()->id,
            'from_date' => $from,
            'to_date' => $to,
            'title' => $title,
            'type' => $data['type'],
            'options' => $options,
            'pdf_disk' => 'public',
            'pdf_path' => $fileName,
            'share_token' => $token,
            'share_expires_at' => $expiresAt,
            'share_password_hash' => $pwdHash,
        ]);

        $activity->logModel($request, $project->organization_id, $project->id, $request->user()->id, 'generated', 'reports', null, $report);

        // Notify owner/pm
        $recipients = $project->members()
            ->wherePivotIn('role', ['owner','pm'])
            ->get();

        foreach ($recipients as $u) {
            $u->notify(new \App\Notifications\ReportGenerated($project, $report));
        }

        // Send Email if requested
        if (!empty($data['send_email']) && !empty($data['email_to'])) {
             try {
                Mail::to($data['email_to'])->send(new ProjectReportMail(
                    projectName: $project->name,
                    title: $report->title,
                    message: "Here is your requested report.",
                    pdfBytes: $pdfBytes
                ));
            } catch (\Exception $e) {
                // log but don't fail the request
            }
        }

        return back()->with('success', 'Report generated.');
    }

    public function download(Request $request, Project $project, ProjectReport $report)
    {
        Gate::authorize('view', $project);
        abort_unless($report->project_id === $project->id, 404);

        return Storage::disk($report->pdf_disk)->download($report->pdf_path, $report->title . '.pdf');
    }

    public function destroy(Request $request, Project $project, ProjectReport $report)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        Gate::authorize('delete', [$report, $project]); // Assuming Policy exists, or use logic
        abort_unless($report->project_id === $project->id, 404);

        Storage::disk($report->pdf_disk)->delete($report->pdf_path);
        $report->delete();

        return back()->with('success', 'Report deleted.');
    }

    public function share(Request $request, string $token)
    {
        $report = ProjectReport::query()
            ->where('share_token', $token)
            ->with('project:id,name,organization_id')
            ->firstOrFail();

        if ($report->share_expires_at && now()->greaterThan($report->share_expires_at)) {
            abort(403);
        }

        // If password protected, require password session
        $sessionKey = "report_access_{$report->id}";
        $hasAccess = $request->session()->get($sessionKey) === true;

        if ($report->share_password_hash && !$hasAccess) {
            return inertia('Public/ReportShare', [
                'token' => $token,
                'title' => $report->title,
                'projectName' => $report->project->name,
                'from' => $report->from_date->toDateString(),
                'to' => $report->to_date->toDateString(),
                'requiresPassword' => true,
                'branding' => null, // Hide branding until unlock if desired, or show it. Let's show it to identify sender.
                'evidence' => null,
            ]);
        }

        // Load branding & evidence
        $project = $report->project()->with('organization')->first();
        $org = $project->organization;
        
        $from = $report->from_date->toDateString();
        $to = $report->to_date->toDateString();

        // Evidence: Attachments (across project) within range
        $attachments = Attachment::query()
            ->where('project_id', $project->id)
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->with('uploader:id,name')
            ->orderByDesc('id')
            ->limit(80)
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'name' => $a->original_name,
                'caption' => $a->caption,
                'mime' => $a->mime,
                'size' => (int) $a->size,
                'url' => $a->url(),
                'created_at' => $a->created_at->toDateTimeString(),
                'uploader' => $a->uploader?->name,
            ])->values();

        // Evidence: Media within range
        $media = ProjectMedia::query()
            ->where('project_id', $project->id)
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->orderByDesc('id')
            ->limit(80)
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'caption' => $m->caption,
                'url' => $m->url ?? null,
                'type' => $m->type ?? null,
                'created_at' => $m->created_at->toDateTimeString(),
            ])->values();

        // Auto download if no password or password verified
        // But better to show the page with "Download" button to avoid immediate download on mobile
        return inertia('Public/ReportShare', [
            'token' => $token,
            'title' => $report->title,
            'projectName' => $report->project->name,
            'from' => $report->from_date->toDateString(),
            'to' => $report->to_date->toDateString(),
            'requiresPassword' => false,
            'branding' => [
                'name' => $org->name, // using name as fallback brand_name if not distinct
                'logo' => null, // Add logo logic if org has logo field
                'email' => $org->email,
                'phone' => null, // Add phone if org has it
                'address' => null, // Add address if org has it
            ],
            'evidence' => [
                'attachments' => $attachments,
                'media' => $media,
            ],
        ]);
    }

    public function shareUnlock(Request $request, string $token)
    {
        $report = ProjectReport::query()
            ->where('share_token', $token)
            ->firstOrFail();

        if ($report->share_expires_at && now()->greaterThan($report->share_expires_at)) {
            abort(403);
        }

        $data = $request->validate([
            'password' => ['required', 'string', 'max:100'],
        ]);

        if (!$report->share_password_hash || !Hash::check($data['password'], $report->share_password_hash)) {
            return back()->with('error', 'Wrong password.');
        }

        $request->session()->put("report_access_{$report->id}", true);

        return redirect()->route('reports.share', ['token' => $token]);
    }

    public function shareDownload(Request $request, string $token)
    {
        $report = ProjectReport::query()
            ->where('share_token', $token)
            ->firstOrFail();

        if ($report->share_expires_at && now()->greaterThan($report->share_expires_at)) {
            abort(403);
        }

        if ($report->share_password_hash) {
            $hasAccess = $request->session()->get("report_access_{$report->id}") === true;
            abort_unless($hasAccess, 403);
        }

        return Storage::disk($report->pdf_disk)->download($report->pdf_path, 'BuildFlow-Report.pdf');
    }

    public function email(Request $request, Project $project, ActivityLogger $activity)
    {
        Gate::authorize('view', $project);
        Gate::authorize('editContent', $project);
        Gate::authorize('email', [ProjectReport::class, $project]);

        $data = $request->validate([
            'report_id' => ['required', 'integer'],
            'to' => ['required', 'email'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $report = ProjectReport::query()
            ->where('project_id', $project->id)
            ->where('id', $data['report_id'])
            ->firstOrFail();

        $pdfBytes = Storage::disk($report->pdf_disk)->get($report->pdf_path);

        try {
            Mail::to($data['to'])->send(new ProjectReportMail(
                projectName: $project->name,
                title: $report->title ?: 'BuildFlow Report',
                message: $data['message'] ?? null,
                pdfBytes: $pdfBytes
            ));

            $activity->log($request, $project->organization_id, $project->id, $request->user()->id, 'sent_email', 'reports', $report->id, ['to' => $data['to']], null);

            return back()->with('success', 'Report sent via email.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send email. Please check SMTP settings.');
        }
    }
}
