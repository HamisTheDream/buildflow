<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfileSettingsController;
use App\Http\Controllers\Auth\InviteAcceptanceController;
use App\Http\Controllers\OrganizationMembersController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/invites/{token}', [InviteAcceptanceController::class, 'show'])->name('invites.show');


Route::middleware('guest')->group(function () {
    Route::get('/invites/{token}', [InviteAcceptanceController::class, 'show'])->name('invites.show');
    Route::post('/invites/{token}', [InviteAcceptanceController::class, 'store'])->name('invites.accept');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/app/settings/profile', [ProfileSettingsController::class, 'edit'])->name('settings.profile');
Route::patch('/app/settings/profile', [ProfileSettingsController::class, 'updateProfile'])->name('settings.profile.update');
Route::patch('/app/settings/security/password', [ProfileSettingsController::class, 'updatePassword'])->name('settings.password.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Billing & Profile (Accessible even if expired)
    Route::get('/app/billing', [App\Http\Controllers\App\OrgBillingController::class, 'index'])->name('billing');
    Route::post('/app/billing/upgrade', [App\Http\Controllers\App\PaystackBillingController::class, 'upgrade'])->name('billing.upgrade');
    Route::get('/app/billing/paystack/callback', [App\Http\Controllers\App\PaystackBillingController::class, 'callback'])->name('billing.paystack.callback');
    
    // Support
    Route::get('/app/support', [App\Http\Controllers\App\Support\SupportTicketController::class, 'create'])->name('app.support.create');
    Route::post('/app/support', [App\Http\Controllers\App\Support\SupportTicketController::class, 'store'])->name('app.support.store');
    
    // Protected App Routes (Enforce Subscription)
    Route::middleware([\App\Http\Middleware\EnsureOrgActiveAccess::class, 'org.access'])->group(function () {
        Route::get('/app/dashboard', function () {
            return Inertia::render('App/Dashboard');
        })->name('app.dashboard');

        Route::get('/dashboard', function () {
            return redirect()->route('app.dashboard');
        })->name('dashboard');

        Route::get('/app/organization/members', [OrganizationMembersController::class, 'index'])->name('org.members');
        Route::patch('/app/organization/members/{member}', [OrganizationMembersController::class, 'update'])->name('org.members.update');
        Route::post('/app/organization/invites', [OrganizationMembersController::class, 'invite'])->name('org.invites.create');
        Route::delete('/app/organization/invites/{invite}', [OrganizationMembersController::class, 'revoke'])->name('org.invites.revoke');
        Route::post('/app/organization/invites/{invite}/resend', [OrganizationMembersController::class, 'resend'])
        ->name('org.invites.resend');
        Route::post('/app/invites/{token}/accept', [InviteAcceptanceController::class, 'acceptWhileLoggedIn'])
            ->name('invites.accept.auth');

        Route::get('/app/projects', [App\Http\Controllers\App\ProjectController::class, 'index'])->name('projects.index');
        Route::get('/app/projects/create', [App\Http\Controllers\App\ProjectController::class, 'create'])->name('projects.create');
        Route::post('/app/projects', [App\Http\Controllers\App\ProjectController::class, 'store'])->name('projects.store');
        Route::get('/app/projects/{project}', [App\Http\Controllers\App\ProjectController::class, 'show'])->name('projects.show');
        Route::get('/app/projects/{project}/activity', [App\Http\Controllers\App\ProjectActivityController::class, 'index'])->name('projects.activity');

        Route::get('/app/projects/{project}/team', [App\Http\Controllers\App\ProjectTeamController::class, 'index'])->name('projects.team');
        Route::post('/app/projects/{project}/team', [App\Http\Controllers\App\ProjectTeamController::class, 'store'])->name('projects.team.store');
        Route::patch('/app/projects/{project}/team/{user}', [App\Http\Controllers\App\ProjectTeamController::class, 'update'])->name('projects.team.update');
        Route::delete('/app/projects/{project}/team/{user}', [App\Http\Controllers\App\ProjectTeamController::class, 'destroy'])->name('projects.team.destroy');

        Route::get('/app/projects/{project}/today', [App\Http\Controllers\App\ProjectTodayController::class, 'show'])->name('projects.today');
        Route::post('/app/projects/{project}/today', [App\Http\Controllers\App\ProjectTodayController::class, 'upsert'])->name('projects.today.upsert');

        // Placeholders
        Route::get('/app/projects/{project}/logs', [App\Http\Controllers\App\ProjectLogsController::class, 'index'])->name('projects.logs');
        Route::post('/app/projects/{project}/logs', [App\Http\Controllers\App\ProjectLogsController::class, 'store'])->name('projects.logs.store');
        Route::patch('/app/projects/{project}/logs/{log}', [App\Http\Controllers\App\ProjectLogsController::class, 'update'])->name('projects.logs.update');
        Route::delete('/app/projects/{project}/logs/{log}', [App\Http\Controllers\App\ProjectLogsController::class, 'destroy'])->name('projects.logs.destroy');

        Route::get('/app/projects/{project}/tasks', [App\Http\Controllers\App\ProjectTasksController::class, 'index'])->name('projects.tasks');
        Route::post('/app/projects/{project}/tasks', [App\Http\Controllers\App\ProjectTasksController::class, 'store'])->name('projects.tasks.store');
        Route::patch('/app/projects/{project}/tasks/{task}', [App\Http\Controllers\App\ProjectTasksController::class, 'update'])->name('projects.tasks.update');
        Route::delete('/app/projects/{project}/tasks/{task}', [App\Http\Controllers\App\ProjectTasksController::class, 'destroy'])->name('projects.tasks.destroy');

        Route::get('/app/projects/{project}/issues', [App\Http\Controllers\App\ProjectIssuesController::class, 'index'])->name('projects.issues');
        Route::post('/app/projects/{project}/issues', [App\Http\Controllers\App\ProjectIssuesController::class, 'store'])->name('projects.issues.store');
        Route::patch('/app/projects/{project}/issues/{issue}', [App\Http\Controllers\App\ProjectIssuesController::class, 'update'])->name('projects.issues.update');
        Route::delete('/app/projects/{project}/issues/{issue}', [App\Http\Controllers\App\ProjectIssuesController::class, 'destroy'])->name('projects.issues.destroy');

        Route::get('/app/projects/{project}/media', [App\Http\Controllers\App\ProjectMediaController::class, 'index'])->name('projects.media');
        Route::post('/app/projects/{project}/media', [App\Http\Controllers\App\ProjectMediaController::class, 'store'])->name('projects.media.store');
        Route::patch('/app/projects/{project}/media/{media}', [App\Http\Controllers\App\ProjectMediaController::class, 'update'])->name('projects.media.update');
        Route::delete('/app/projects/{project}/media/{media}', [App\Http\Controllers\App\ProjectMediaController::class, 'destroy'])->name('projects.media.destroy');

        Route::get('/app/projects/{project}/costs', [App\Http\Controllers\App\ProjectCostsController::class, 'index'])->name('projects.costs');
        Route::post('/app/projects/{project}/costs', [App\Http\Controllers\App\ProjectCostsController::class, 'store'])->name('projects.costs.store');
        Route::patch('/app/projects/{project}/costs/{cost}', [App\Http\Controllers\App\ProjectCostsController::class, 'update'])->name('projects.costs.update');
        Route::delete('/app/projects/{project}/costs/{cost}', [App\Http\Controllers\App\ProjectCostsController::class, 'destroy'])->name('projects.costs.destroy');

        Route::get('/app/projects/{project}/reports', [App\Http\Controllers\App\ProjectReportsController::class, 'index'])->name('projects.reports');
        Route::post('/app/projects/{project}/reports/generate', [App\Http\Controllers\App\ProjectReportsController::class, 'generate'])->name('projects.reports.generate');
        Route::post('/app/projects/{project}/reports/email', [App\Http\Controllers\App\ProjectReportsController::class, 'email'])->name('projects.reports.email');
        Route::get('/app/projects/{project}/reports/{report}/download', [App\Http\Controllers\App\ProjectReportsController::class, 'download'])->name('projects.reports.download');
        Route::delete('/app/projects/{project}/reports/{report}', [App\Http\Controllers\App\ProjectReportsController::class, 'destroy'])->name('projects.reports.destroy');

        Route::post('/app/projects/{project}/attachments', [App\Http\Controllers\App\ProjectAttachmentsController::class, 'store'])->name('projects.attachments.store');
        Route::delete('/app/projects/{project}/attachments/{attachment}', [App\Http\Controllers\App\ProjectAttachmentsController::class, 'destroy'])->name('projects.attachments.destroy');

        Route::get('/app/projects/{project}/export/tasks', [App\Http\Controllers\App\ProjectExportsController::class, 'tasks'])->name('projects.export.tasks');
        Route::get('/app/projects/{project}/export/issues', [App\Http\Controllers\App\ProjectExportsController::class, 'issues'])->name('projects.export.issues');
        Route::get('/app/projects/{project}/export/costs', [App\Http\Controllers\App\ProjectExportsController::class, 'costs'])->name('projects.export.costs');
        Route::get('/app/projects/{project}/export/logs', [App\Http\Controllers\App\ProjectExportsController::class, 'logs'])->name('projects.export.logs');
    });
});

// Public share link (no auth)
// Public share link (no auth)
Route::get('/share/report/{token}', [App\Http\Controllers\App\ProjectReportsController::class, 'share'])->name('reports.share');
Route::post('/share/report/{token}/unlock', [App\Http\Controllers\App\ProjectReportsController::class, 'shareUnlock'])->name('reports.share.unlock');
Route::get('/share/report/{token}/download', [App\Http\Controllers\App\ProjectReportsController::class, 'shareDownload'])->name('reports.share.download');

// Owner / Super Admin
require __DIR__.'/owner.php';

Route::post('/webhooks/paystack', [App\Http\Controllers\Webhooks\PaystackWebhookController::class, 'handle'])
    ->name('webhooks.paystack');

require __DIR__.'/auth.php';
