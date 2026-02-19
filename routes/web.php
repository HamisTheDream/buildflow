<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfileSettingsController;
use App\Http\Controllers\Auth\InviteAcceptanceController;
use App\Http\Controllers\OrganizationMembersController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', function () {
    $plans = \App\Models\Plan::all();
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'plans' => $plans,
    ]);
});


// Blog routes are defined at the bottom with the BlogController

Route::get('/contact', function () {
    return Inertia::render('Public/Contact');
})->name('contact');

Route::get('/terms', function () {
    return Inertia::render('Public/Terms');
})->name('terms');

Route::get('/privacy', function () {
    return Inertia::render('Public/Privacy');
})->name('privacy');


Route::middleware('guest')->group(function () {
    // Guest only routes
});

Route::get('/invites/{token}', [InviteAcceptanceController::class, 'show'])->name('invites.show');
Route::post('/invites/{token}', [InviteAcceptanceController::class, 'store'])->name('invites.accept');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/app/settings/profile', [ProfileSettingsController::class, 'edit'])->name('settings.profile');
    Route::patch('/app/settings/profile', [ProfileSettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::patch('/app/settings/security/password', [ProfileSettingsController::class, 'updatePassword'])->name('settings.password.update');
});

Route::middleware(['auth'])->group(function () {
    // Billing & Profile (Accessible even if expired)
    Route::get('/app/billing', [App\Http\Controllers\App\OrgBillingController::class, 'index'])->name('billing');
    Route::post('/app/billing/upgrade', [App\Http\Controllers\App\PaystackBillingController::class, 'upgrade'])->name('billing.upgrade');
    Route::get('/app/billing/paystack/callback', [App\Http\Controllers\App\PaystackBillingController::class, 'callback'])->name('billing.paystack.callback');

    // Support
    Route::get('/app/support', [App\Http\Controllers\App\Support\SupportTicketController::class, 'index'])->name('app.support.index');
    Route::get('/app/support/create', [App\Http\Controllers\App\Support\SupportTicketController::class, 'create'])->name('app.support.create');
    Route::post('/app/support', [App\Http\Controllers\App\Support\SupportTicketController::class, 'store'])->name('app.support.store');
    Route::get('/app/support/{ticket}', [App\Http\Controllers\App\Support\SupportTicketController::class, 'show'])->name('app.support.show');
    Route::post('/app/support/{ticket}/reply', [App\Http\Controllers\App\Support\SupportTicketController::class, 'storeReply'])->name('app.support.reply');

    // Protected App Routes (Enforce Subscription)
    Route::middleware([\App\Http\Middleware\EnsureOrgHasAccess::class])->group(function () {
        Route::get('/app/dashboard', App\Http\Controllers\App\DashboardController::class)->name('app.dashboard');

        Route::get('/dashboard', function () {
            return redirect()->route('app.dashboard');
        })->name('dashboard');

        // Notifications API
        Route::get('/app/notifications', [App\Http\Controllers\App\NotificationsController::class, 'index'])->name('notifications.index');
        Route::post('/app/notifications/{notification}/read', [App\Http\Controllers\App\NotificationsController::class, 'markRead'])->name('notifications.read');
        Route::post('/app/notifications/mark-all-read', [App\Http\Controllers\App\NotificationsController::class, 'markAllRead'])->name('notifications.markAllRead');

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

        // PDF Exports (rate limited - heavy operations)
        Route::middleware('throttle:heavy')->group(function () {
            Route::get('/app/projects/{project}/reports/{report}/pdf', [App\Http\Controllers\App\ReportPdfController::class, 'download'])->name('projects.reports.pdf');
            Route::get('/app/projects/{project}/summary/pdf', [App\Http\Controllers\App\ReportPdfController::class, 'projectSummary'])->name('projects.summary.pdf');
        });

        // Attachments (rate limited - uploads)
        Route::middleware('throttle:uploads')->group(function () {
            Route::post('/app/projects/{project}/attachments', [App\Http\Controllers\App\ProjectAttachmentsController::class, 'store'])->name('projects.attachments.store');
        });
        Route::delete('/app/projects/{project}/attachments/{attachment}', [App\Http\Controllers\App\ProjectAttachmentsController::class, 'destroy'])->name('projects.attachments.destroy');

        Route::get('/app/projects/{project}/export/tasks', [App\Http\Controllers\App\ProjectExportsController::class, 'tasks'])->name('projects.export.tasks');
        Route::get('/app/projects/{project}/export/issues', [App\Http\Controllers\App\ProjectExportsController::class, 'issues'])->name('projects.export.issues');
        Route::get('/app/projects/{project}/export/costs', [App\Http\Controllers\App\ProjectExportsController::class, 'costs'])->name('projects.export.costs');
        Route::get('/app/projects/{project}/export/logs', [App\Http\Controllers\App\ProjectExportsController::class, 'logs'])->name('projects.export.logs');

        // CRM Module
        Route::get('app/crm', [\App\Http\Controllers\CRM\DashboardController::class, 'index'])
            ->name('crm.dashboard');

        Route::resource('app/crm/properties', \App\Http\Controllers\CRM\PropertyController::class)
            ->names('crm.properties')
            ->except(['index', 'show']);

        Route::get('app/crm/properties/{property}/units/create', [\App\Http\Controllers\CRM\PropertyUnitController::class, 'create'])
            ->name('crm.properties.units.create');
        Route::post('app/crm/properties/{property}/units', [\App\Http\Controllers\CRM\PropertyUnitController::class, 'store'])
            ->name('crm.properties.units.store');
        Route::delete('app/crm/properties/{property}/units/{unit}', [\App\Http\Controllers\CRM\PropertyUnitController::class, 'destroy'])
            ->name('crm.properties.units.destroy');

        Route::resource('app/crm/leads', \App\Http\Controllers\CRM\LeadController::class)
            ->names('crm.leads')
            ->except(['index', 'show']);

        Route::resource('app/crm/deals', \App\Http\Controllers\CRM\DealController::class)
            ->names('crm.deals')
            ->except(['index', 'show']);
    });

    // Finance Routes
    Route::middleware(['auth', \App\Http\Middleware\EnsureOrgActiveAccess::class, 'org.access'])->prefix('app/finance')->name('finance.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Finance\DashboardController::class, 'index'])->name('dashboard');

        Route::get('invoices/{invoice}/pdf', [\App\Http\Controllers\Finance\InvoiceController::class, 'pdf'])->name('invoices.pdf');
        Route::post('invoices/{invoice}/email', [\App\Http\Controllers\Finance\InvoiceController::class, 'email'])->name('invoices.email');
        Route::resource('invoices', \App\Http\Controllers\Finance\InvoiceController::class)->except(['index', 'edit', 'update', 'destroy']);
        Route::resource('expenses', \App\Http\Controllers\Finance\ExpenseController::class)->except(['index', 'show', 'edit']);
        Route::resource('budgets', \App\Http\Controllers\Finance\BudgetController::class)->only(['store', 'destroy']);
    });

    // HR Routes
    Route::middleware(['auth', \App\Http\Middleware\EnsureOrgActiveAccess::class, 'org.access'])->prefix('app/hr')->name('hr.')->group(function () {
        Route::get('/', [\App\Http\Controllers\HR\DashboardController::class, 'index'])->name('dashboard');

        Route::resource('employees', \App\Http\Controllers\HR\EmployeeController::class)->except(['index', 'show', 'destroy']);
        Route::resource('departments', \App\Http\Controllers\HR\DepartmentController::class)->except(['create', 'edit', 'show', 'index']);
        Route::resource('payroll', \App\Http\Controllers\HR\PayrollController::class)->only(['create', 'store', 'show']);
        Route::resource('leaves', \App\Http\Controllers\HR\LeaveController::class)->only(['store', 'update', 'destroy']);
    });
});

// Public share link (no auth)
Route::get('/share/report/{token}', [App\Http\Controllers\App\ProjectReportsController::class, 'share'])->name('reports.share');
Route::post('/share/report/{token}/unlock', [App\Http\Controllers\App\ProjectReportsController::class, 'shareUnlock'])->name('reports.share.unlock');
Route::get('/share/report/{token}/download', [App\Http\Controllers\App\ProjectReportsController::class, 'shareDownload'])->name('reports.share.download');

Route::get('/portal/invoices/{invoice}', [\App\Http\Controllers\Public\InvoiceController::class, 'show'])
    ->name('public.invoice.show')
    ->middleware('signed');

Route::post('/portal/invoices/{invoice}/pay', [\App\Http\Controllers\Public\PaymentController::class, 'pay'])
    ->name('public.invoice.pay')
    ->middleware('signed');

Route::get('/portal/invoices/{invoice}/payment/callback', [\App\Http\Controllers\Public\PaymentController::class, 'callback'])
    ->name('public.invoice.payment.callback');

Route::get('/blog', [App\Http\Controllers\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [App\Http\Controllers\BlogController::class, 'show'])->name('blog.show');


// Owner / Super Admin
require __DIR__ . '/owner.php';

Route::middleware('throttle:webhooks')->post('/webhooks/paystack', [App\Http\Controllers\Webhooks\PaystackWebhookController::class, 'handle'])
    ->name('webhooks.paystack');

require __DIR__ . '/auth.php';
