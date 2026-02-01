<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectCost;
use App\Models\ProjectIssue;
use App\Models\ProjectLog;
use App\Models\ProjectTask;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectExportsController extends Controller
{
    private function streamCsv(string $filename, array $headers, \Closure $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, $headers);
            $rows($out);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function tasks(Request $request, Project $project): StreamedResponse
    {
        $this->authorize('view', $project);

        $filename = 'buildflow_tasks_project_'.$project->id.'_'.now()->format('Ymd_His').'.csv';

        return $this->streamCsv($filename, [
            'ID','Title','Status','Priority','Due Date','Assignee','Created At','Updated At'
        ], function ($out) use ($project) {
            ProjectTask::query()
                ->where('project_id', $project->id)
                ->with('assignee:id,name,email')
                ->orderBy('id')
                ->chunk(500, function ($chunk) use ($out) {
                    foreach ($chunk as $t) {
                        fputcsv($out, [
                            $t->id,
                            $t->title,
                            $t->status,
                            $t->priority,
                            $t->due_date?->toDateString(),
                            $t->assignee?->name,
                            $t->created_at?->toDateTimeString(),
                            $t->updated_at?->toDateTimeString(),
                        ]);
                    }
                });
        });
    }

    public function issues(Request $request, Project $project): StreamedResponse
    {
        $this->authorize('view', $project);

        $filename = 'buildflow_issues_project_'.$project->id.'_'.now()->format('Ymd_His').'.csv';

        return $this->streamCsv($filename, [
            'ID','Title','Status','Severity','Due Date','Assignee','Resolved At','Created At','Updated At'
        ], function ($out) use ($project) {
            ProjectIssue::query()
                ->where('project_id', $project->id)
                ->with('assignee:id,name,email')
                ->orderBy('id')
                ->chunk(500, function ($chunk) use ($out) {
                    foreach ($chunk as $i) {
                        fputcsv($out, [
                            $i->id,
                            $i->title,
                            $i->status,
                            $i->severity,
                            $i->due_date?->toDateString(),
                            $i->assignee?->name,
                            $i->resolved_at?->toDateTimeString(),
                            $i->created_at?->toDateTimeString(),
                            $i->updated_at?->toDateTimeString(),
                        ]);
                    }
                });
        });
    }

    public function costs(Request $request, Project $project): StreamedResponse
    {
        $this->authorize('view', $project);

        $filename = 'buildflow_costs_project_'.$project->id.'_'.now()->format('Ymd_His').'.csv';

        return $this->streamCsv($filename, [
            'ID','Date','Category','Vendor','Description','Amount','Created At','Updated At'
        ], function ($out) use ($project) {
            ProjectCost::query()
                ->where('project_id', $project->id)
                ->orderBy('cost_date')
                ->orderBy('id')
                ->chunk(500, function ($chunk) use ($out) {
                    foreach ($chunk as $c) {
                        fputcsv($out, [
                            $c->id,
                            $c->cost_date?->toDateString(),
                            $c->category,
                            $c->vendor,
                            $c->description,
                            $c->amount,
                            $c->created_at?->toDateTimeString(),
                            $c->updated_at?->toDateTimeString(),
                        ]);
                    }
                });
        });
    }

    public function logs(Request $request, Project $project): StreamedResponse
    {
        $this->authorize('view', $project);

        $filename = 'buildflow_logs_project_'.$project->id.'_'.now()->format('Ymd_His').'.csv';

        return $this->streamCsv($filename, [
            'ID','Type','Title','Body','Log Date','Log Time','User','Created At'
        ], function ($out) use ($project) {
            ProjectLog::query()
                ->where('project_id', $project->id)
                ->with('user:id,name,email')
                ->orderBy('log_date')
                ->orderBy('id')
                ->chunk(500, function ($chunk) use ($out) {
                    foreach ($chunk as $l) {
                        fputcsv($out, [
                            $l->id,
                            $l->type,
                            $l->title,
                            $l->body,
                            $l->log_date?->toDateString(),
                            $l->log_time,
                            $l->user?->name,
                            $l->created_at?->toDateTimeString(),
                        ]);
                    }
                });
        });
    }
}
