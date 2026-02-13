<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Owner\Auth\OwnerAuthController;
use App\Http\Controllers\Owner\CMS\OwnerAnnouncementsController;
use App\Http\Controllers\Owner\Support\OwnerSupportController;
use App\Http\Controllers\Owner\Admins\OwnerAdminsController;
use App\Http\Controllers\Owner\OwnerDashboardController;

Route::middleware('web')->group(function () {

    // Guest-only (owner)
    Route::middleware('guest:owner')->group(function () {
        Route::get('/owner/login', [OwnerAuthController::class, 'showLogin'])->name('owner.login');
        Route::post('/owner/login', [OwnerAuthController::class, 'login'])->name('owner.login.store');
    });

    // Auth-only (owner)
    Route::middleware(['auth:owner', 'owner.active'])->group(function () {
        Route::post('/owner/logout', [OwnerAuthController::class, 'logout'])->name('owner.logout');

        Route::get('/owner', fn() => redirect('/owner/dashboard'));
        Route::get('/owner/dashboard', [OwnerDashboardController::class, 'index'])->name('owner.dashboard');

        // Announcements
        Route::get('/owner/announcements', [OwnerAnnouncementsController::class, 'index'])->name('owner.announcements.index');
        Route::get('/owner/announcements/create', [OwnerAnnouncementsController::class, 'create'])->name('owner.announcements.create');
        Route::post('/owner/announcements', [OwnerAnnouncementsController::class, 'store'])->name('owner.announcements.store');
        Route::get('/owner/announcements/{announcement}/edit', [OwnerAnnouncementsController::class, 'edit'])->name('owner.announcements.edit');
        Route::patch('/owner/announcements/{announcement}', [OwnerAnnouncementsController::class, 'update'])->name('owner.announcements.update');
        Route::delete('/owner/announcements/{announcement}', [OwnerAnnouncementsController::class, 'destroy'])->name('owner.announcements.destroy');

        // Support
        Route::get('/owner/support', [OwnerSupportController::class, 'index'])->name('owner.support.index');
        Route::get('/owner/support/{ticket}', [OwnerSupportController::class, 'show'])->name('owner.support.show');
        Route::patch('/owner/support/{ticket}', [OwnerSupportController::class, 'update'])->name('owner.support.update');
        Route::post('/owner/support/{ticket}/notes', [OwnerSupportController::class, 'addNote'])->name('owner.support.notes');
        Route::post('/owner/support/{ticket}/reply', [OwnerSupportController::class, 'storeReply'])->name('owner.support.reply');

        Route::get('/owner/organizations', [\App\Http\Controllers\Owner\Organizations\OwnerOrganizationsController::class, 'index'])->name('owner.organizations.index');
        Route::get('/owner/organizations/{organization}', [\App\Http\Controllers\Owner\Organizations\OwnerOrganizationsController::class, 'show'])->name('owner.organizations.show');

        // actions
        Route::post('/owner/organizations/{organization}/extend-trial', [\App\Http\Controllers\Owner\Organizations\OwnerOrganizationsController::class, 'extendTrial'])->name('owner.organizations.extend_trial');
        Route::post('/owner/organizations/{organization}/comp-plan', [\App\Http\Controllers\Owner\Organizations\OwnerOrganizationsController::class, 'compPlan'])->name('owner.organizations.comp_plan');
        Route::post('/owner/organizations/{organization}/downgrade', [\App\Http\Controllers\Owner\Organizations\OwnerOrganizationsController::class, 'downgrade'])->name('owner.organizations.downgrade');
        Route::post('/owner/organizations/{organization}/suspend', [\App\Http\Controllers\Owner\Organizations\OwnerOrganizationsController::class, 'suspend'])->name('owner.organizations.suspend');
        Route::post('/owner/organizations/{organization}/reactivate', [\App\Http\Controllers\Owner\Organizations\OwnerOrganizationsController::class, 'reactivate'])->name('owner.organizations.reactivate');

        // CRM
        Route::post('/owner/organizations/{organization}/notes', [\App\Http\Controllers\Owner\CRM\OrganizationNoteController::class, 'store'])->name('owner.organizations.notes.store');
        Route::delete('/owner/notes/{note}', [\App\Http\Controllers\Owner\CRM\OrganizationNoteController::class, 'destroy'])->name('owner.notes.destroy');
        Route::post('/owner/organizations/{organization}/tasks', [\App\Http\Controllers\Owner\CRM\OrganizationTaskController::class, 'store'])->name('owner.organizations.tasks.store');
        Route::patch('/owner/tasks/{task}', [\App\Http\Controllers\Owner\CRM\OrganizationTaskController::class, 'update'])->name('owner.tasks.update');
        Route::delete('/owner/tasks/{task}', [\App\Http\Controllers\Owner\CRM\OrganizationTaskController::class, 'destroy'])->name('owner.tasks.destroy');
        Route::patch('/owner/organizations/{organization}/stage', [\App\Http\Controllers\Owner\Organizations\OwnerOrganizationsController::class, 'updateStage'])->name('owner.organizations.stage.update');

        // Billing Ops
        Route::get('/owner/billing/payments', [\App\Http\Controllers\Owner\Billing\OwnerPaymentsController::class, 'index'])->name('owner.billing.payments.index');
        Route::get('/owner/billing/payments/{payment}', [\App\Http\Controllers\Owner\Billing\OwnerPaymentsController::class, 'show'])->name('owner.billing.payments.show');
        Route::post('/owner/billing/payments/verify', [\App\Http\Controllers\Owner\Billing\OwnerPaymentsController::class, 'verifyReference'])->name('owner.billing.payments.verify');
        Route::post('/owner/billing/payments/{payment}/notes', [\App\Http\Controllers\Owner\Billing\OwnerPaymentsController::class, 'addNote'])->name('owner.billing.payments.notes');

        Route::get('/owner/billing/webhooks', [\App\Http\Controllers\Owner\Billing\OwnerWebhooksController::class, 'index'])->name('owner.billing.webhooks.index');
        Route::get('/owner/billing/webhooks/{event}', [\App\Http\Controllers\Owner\Billing\OwnerWebhooksController::class, 'show'])->name('owner.billing.webhooks.show');

        // Platform Settings
        Route::get('/owner/settings', [\App\Http\Controllers\Owner\SettingsController::class, 'index'])->name('owner.settings.index');
        Route::post('/owner/settings', [\App\Http\Controllers\Owner\SettingsController::class, 'update'])->name('owner.settings.update');

        // Blog Management
        Route::resource('owner/blog', \App\Http\Controllers\Owner\BlogController::class);

        // Admins
        Route::get('/owner/admins', [OwnerAdminsController::class, 'index'])->name('owner.admins.index');

        // Notifications
        Route::get('/owner/notifications', [\App\Http\Controllers\Owner\OwnerNotificationsController::class, 'index']);
        Route::post('/owner/notifications/{id}/read', [\App\Http\Controllers\Owner\OwnerNotificationsController::class, 'markAsRead']);
        Route::post('/owner/notifications/mark-all-read', [\App\Http\Controllers\Owner\OwnerNotificationsController::class, 'markAllAsRead']);

        // Finance Module
        Route::get('/owner/finance', [\App\Http\Controllers\Owner\Finance\FinanceDashboardController::class, 'index'])->name('owner.finance.index');
        Route::resource('/owner/finance/deals', \App\Http\Controllers\Owner\Finance\DealsController::class, ['as' => 'owner.finance']);
        Route::resource('/owner/finance/expenses', \App\Http\Controllers\Owner\Finance\ExpensesController::class, ['as' => 'owner.finance']);
        Route::resource('/owner/finance/salaries', \App\Http\Controllers\Owner\Finance\SalariesController::class, ['as' => 'owner.finance']);

        Route::get('/owner/admins/create', [OwnerAdminsController::class, 'create'])->name('owner.admins.create');
        Route::post('/owner/admins', [OwnerAdminsController::class, 'store'])->name('owner.admins.store');
        Route::patch('/owner/admins/{admin}/toggle-super', [OwnerAdminsController::class, 'toggleSuper'])->name('owner.admins.toggle_super');
        Route::patch('/owner/admins/{admin}/toggle-active', [OwnerAdminsController::class, 'toggleActive'])->name('owner.admins.toggle_active');

        // Profile
        Route::get('/owner/profile', [\App\Http\Controllers\Owner\Profile\OwnerProfileController::class, 'edit'])->name('owner.profile.edit');
        Route::patch('/owner/profile', [\App\Http\Controllers\Owner\Profile\OwnerProfileController::class, 'update'])->name('owner.profile.update');
        Route::patch('/owner/profile/password', [\App\Http\Controllers\Owner\Profile\OwnerProfileController::class, 'updatePassword'])->name('owner.profile.password');
        Route::post('/owner/profile/avatar', [\App\Http\Controllers\Owner\Profile\OwnerProfileController::class, 'updateAvatar'])->name('owner.profile.avatar');
        Route::delete('/owner/profile/avatar', [\App\Http\Controllers\Owner\Profile\OwnerProfileController::class, 'deleteAvatar'])->name('owner.profile.avatar.delete');

        // Platform HR
        Route::resource('owner/hr/employees', \App\Http\Controllers\Owner\HR\OwnerEmployeesController::class)->names('owner.hr.employees');
        Route::resource('owner/hr/departments', \App\Http\Controllers\Owner\HR\OwnerDepartmentsController::class)->names('owner.hr.departments')->only(['index', 'store', 'update', 'destroy']);
    });
});
