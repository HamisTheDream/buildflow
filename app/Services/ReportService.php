<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectCost;
use App\Models\ProjectIssue;
use App\Models\ProjectLog;
use App\Models\ProjectTask;
use App\Models\TodayLog;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportService
{
    public function buildPayload(Project $project, string $from, string $to, array $options): array
    {
        $todayLogs = TodayLog::query()
            ->where('project_id', $project->id)
            ->whereBetween('log_date', [$from, $to])
            ->with('user:id,name,email')
            ->orderBy('log_date')
            ->get()
            ->map(fn($l) => [
                'id' => $l->id,
                'log_date' => $l->log_date->toDateString(),
                'user' => ['id' => $l->user->id, 'name' => $l->user->name, 'email' => $l->user->email],
                'work_done' => $l->work_done,
                'blockers' => $l->blockers,
                'next_steps' => $l->next_steps,
                'progress_percent' => $l->progress_percent,
                'weather' => $l->weather,
            ]);

        $logs = ProjectLog::query()
            ->where('project_id', $project->id)
            ->whereBetween('log_date', [$from, $to])
            ->with('user:id,name,email')
            ->orderBy('log_date')
            ->orderBy('id')
            ->get()
            ->map(fn($l) => [
                'id' => $l->id,
                'log_date' => $l->log_date->toDateString(),
                'log_time' => $l->log_time ? substr($l->log_time, 0, 5) : '',
                'type' => $l->type,
                'title' => $l->title,
                'body' => $l->body,
                'user' => ['id' => $l->user->id, 'name' => $l->user->name, 'email' => $l->user->email],
            ]);

        $tasks = ProjectTask::query()
            ->where('project_id', $project->id)
            ->with('assignee:id,name,email')
            ->orderBy('status')
            ->orderBy('due_date')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'status' => $t->status,
                'priority' => $t->priority,
                'due_date' => $t->due_date?->toDateString(),
                'assignee' => $t->assignee ? [
                    'id' => $t->assignee->id,
                    'name' => $t->assignee->name,
                    'email' => $t->assignee->email,
                ] : null,
            ]);

        $issues = ProjectIssue::query()
            ->where('project_id', $project->id)
            ->with('assignee:id,name,email')
            ->orderBy('status')
            ->orderBy('severity')
            ->get()
            ->map(fn($i) => [
                'id' => $i->id,
                'title' => $i->title,
                'status' => $i->status,
                'severity' => $i->severity,
                'due_date' => $i->due_date?->toDateString(),
                'assignee' => $i->assignee ? [
                    'id' => $i->assignee->id,
                    'name' => $i->assignee->name,
                    'email' => $i->assignee->email,
                ] : null,
                'resolved_at' => $i->resolved_at?->toDateTimeString(),
            ]);

        $costs = ProjectCost::query()
            ->where('project_id', $project->id)
            ->whereBetween('cost_date', [$from, $to])
            ->orderBy('cost_date')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'cost_date' => $c->cost_date->toDateString(),
                'category' => $c->category,
                'vendor' => $c->vendor,
                'description' => $c->description,
                'amount' => (float)$c->amount,
            ]);

        $attachments = \App\Models\Attachment::query()
            ->where('project_id', $project->id)
            ->whereBetween('created_at', [\Carbon\Carbon::parse($from)->startOfDay(), \Carbon\Carbon::parse($to)->endOfDay()])
            ->with('uploader:id,name,email')
            ->orderByDesc('id')
            ->limit(80)
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'original_name' => $a->original_name,
                'caption' => $a->caption,
                'mime' => $a->mime,
                'size' => $a->size,
                'url' => $a->url(),
                'created_at' => $a->created_at->toDateTimeString(),
                'uploader' => $a->uploader?->name,
            ]);

        $media = \App\Models\ProjectMedia::query()
            ->where('project_id', $project->id)
            ->whereBetween('created_at', [\Carbon\Carbon::parse($from)->startOfDay(), \Carbon\Carbon::parse($to)->endOfDay()])
            ->orderByDesc('id')
            ->limit(80)
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'caption' => $m->caption,
                'file_url' => $m->url(), // valid method on model
                'created_at' => $m->created_at->toDateTimeString(),
            ]);

        return compact('todayLogs', 'logs', 'tasks', 'issues', 'costs', 'attachments', 'media');
    }

    public function renderPdf(array $viewData): string
    {
        $pdf = Pdf::loadView('reports.project', $viewData)->setPaper('a4', 'portrait');
        return $pdf->output();
    }
}
