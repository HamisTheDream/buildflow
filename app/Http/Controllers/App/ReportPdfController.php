<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReportPdfController extends Controller
{
    public function download(Request $request, Project $project, ProjectReport $report)
    {
        Gate::authorize('view', $project);
        abort_unless($report->project_id === $project->id, 404);
        Gate::authorize('view', $report);

        $report->load(['creator:id,name,email']);

        $pdf = Pdf::loadView('pdf.report', [
            'project' => $project,
            'report' => $report,
            'generatedAt' => now()->format('F j, Y \a\t g:i A'),
        ]);

        $filename = sprintf(
            '%s-%s-%s.pdf',
            str_replace(' ', '-', strtolower($project->name)),
            str_replace(' ', '-', strtolower($report->type)),
            $report->report_date->format('Y-m-d')
        );

        return $pdf->download($filename);
    }

    /**
     * Generate a project summary PDF
     */
    public function projectSummary(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $project->load([
            'team:id,name,email',
        ]);

        // Load recent data
        $recentCosts = $project->costs()
            ->orderByDesc('cost_date')
            ->limit(10)
            ->get();

        $recentLogs = $project->logs()
            ->with('user:id,name')
            ->orderByDesc('log_date')
            ->limit(10)
            ->get();

        $openIssues = $project->issues()
            ->whereIn('status', ['open', 'in_progress'])
            ->get();

        $pdf = Pdf::loadView('pdf.project-summary', [
            'project' => $project,
            'recentCosts' => $recentCosts,
            'recentLogs' => $recentLogs,
            'openIssues' => $openIssues,
            'totalCosts' => $project->costs()->sum('amount'),
            'generatedAt' => now()->format('F j, Y \a\t g:i A'),
        ]);

        $filename = sprintf(
            '%s-summary-%s.pdf',
            str_replace(' ', '-', strtolower($project->name)),
            now()->format('Y-m-d')
        );

        return $pdf->download($filename);
    }
}
