<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\AutomationLog;
use App\Models\AutomationSetting;
use App\Models\Finance\PaymentSchedule;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AutomationController extends Controller
{
    public function index()
    {
        // TEMP DEBUG: Remove after diagnosing 500
        try {
            // Check migration state
            $hasSettings = \Illuminate\Support\Facades\Schema::hasTable('automation_settings');
            $hasLogs = \Illuminate\Support\Facades\Schema::hasTable('automation_logs');
            $hasSchedules = \Illuminate\Support\Facades\Schema::hasTable('payment_schedules');
            $migrations = \Illuminate\Support\Facades\DB::table('migrations')
                ->where('migration', 'like', '%automation%')
                ->pluck('migration');

            return response()->json([
                'tables' => [
                    'automation_settings' => $hasSettings,
                    'automation_logs' => $hasLogs,
                    'payment_schedules' => $hasSchedules,
                ],
                'migration_records' => $migrations,
            ]);

            return $this->indexInner();
        } catch (\Throwable $e) {
            return response()->json([
                'error' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->take(5)->map(fn($t) => ($t['class'] ?? '') . ($t['type'] ?? '') . ($t['function'] ?? ''))->values(),
            ], 500);
        }
    }

    private function indexInner()
    {
        $orgId = auth()->user()->current_organization_id;

        $settings = AutomationSetting::where('organization_id', $orgId)
            ->get()
            ->keyBy('automation_key');

        $automations = [];
        foreach (AutomationSetting::AUTOMATIONS as $key => $meta) {
            $lastRun = AutomationLog::where('organization_id', $orgId)
                ->where('automation_key', $key)
                ->latest()
                ->first();

            $automations[] = array_merge($meta, [
                'key' => $key,
                'is_enabled' => $settings[$key]->is_enabled ?? true,
                'last_run_at' => $lastRun?->created_at,
                'last_action' => $lastRun?->action,
            ]);
        }

        $recentLogs = AutomationLog::where('organization_id', $orgId)
            ->latest()
            ->limit(25)
            ->get();

        $schedules = PaymentSchedule::where('organization_id', $orgId)
            ->with(['clientLead', 'clientUser', 'project'])
            ->latest()
            ->get();

        return Inertia::render('App/Automations/Index', [
            'automations' => $automations,
            'recentLogs' => $recentLogs,
            'schedules' => $schedules,
        ]);
    }

    public function toggle(Request $request, string $key)
    {
        abort_unless(array_key_exists($key, AutomationSetting::AUTOMATIONS), 404);

        $orgId = auth()->user()->current_organization_id;

        $setting = AutomationSetting::firstOrCreate(
            ['organization_id' => $orgId, 'automation_key' => $key],
            ['is_enabled' => true],
        );

        $setting->update(['is_enabled' => ! $setting->is_enabled]);

        return redirect()->back()->with(
            'success',
            AutomationSetting::AUTOMATIONS[$key]['name'] . ($setting->is_enabled ? ' enabled.' : ' disabled.')
        );
    }

    public function storeSchedule(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'client_lead_id' => 'nullable|exists:leads,id',
            'client_user_id' => 'nullable|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
            'deal_id' => 'nullable|exists:deals,id',
            'amount' => 'required|numeric|min:1', // major units, converted to cents
            'frequency' => 'required|in:weekly,monthly,quarterly,yearly',
            'interval' => 'nullable|integer|min:1|max:12',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'max_runs' => 'nullable|integer|min:1|max:360',
            'description' => 'nullable|string',
        ]);

        $orgId = auth()->user()->current_organization_id;

        PaymentSchedule::create([
            'organization_id' => $orgId,
            'name' => $data['name'],
            'client_lead_id' => $data['client_lead_id'] ?? null,
            'client_user_id' => $data['client_user_id'] ?? null,
            'project_id' => $data['project_id'] ?? null,
            'deal_id' => $data['deal_id'] ?? null,
            'amount_cents' => (int) round($data['amount'] * 100),
            'frequency' => $data['frequency'],
            'interval' => $data['interval'] ?? 1,
            'start_date' => $data['start_date'],
            'next_run_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'max_runs' => $data['max_runs'] ?? null,
            'status' => 'active',
            'meta' => ['description' => $data['description'] ?? $data['name']],
        ]);

        return redirect()->back()->with('success', 'Payment schedule created.');
    }

    public function updateSchedule(Request $request, PaymentSchedule $schedule)
    {
        abort_if($schedule->organization_id !== auth()->user()->current_organization_id, 403);

        $data = $request->validate([
            'status' => 'required|in:active,paused,cancelled',
        ]);

        $schedule->update($data);

        return redirect()->back()->with('success', 'Payment schedule updated.');
    }

    public function destroySchedule(PaymentSchedule $schedule)
    {
        abort_if($schedule->organization_id !== auth()->user()->current_organization_id, 403);

        $schedule->delete();

        return redirect()->back()->with('success', 'Payment schedule deleted.');
    }
}
