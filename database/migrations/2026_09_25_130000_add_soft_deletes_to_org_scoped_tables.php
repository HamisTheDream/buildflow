<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds deleted_at (soft deletes) to every organization-scoped table so a
 * Super Admin can soft-delete an organization and all of its data, with
 * restore. Tables that already had softDeletes() in their create migration
 * (leads, deals, invoices, invoice_payments, expenses, budgets,
 * crm_properties, crm_property_units, departments, employees, payrolls,
 * leaves, properties, property_units) are intentionally skipped.
 */
return new class extends Migration
{
    protected array $tables = [
        'organizations',
        'organization_invites',
        'projects',
        'activity_logs',
        'attachments',
        'payments',
        'payment_notes',
        'announcements',
        'support_tickets',
        'support_ticket_notes',
        'organization_notes',
        'organization_tasks',
        'owner_deals',
        'project_media',
        'project_tasks',
        'project_issues',
        'project_logs',
        'project_costs',
        'project_reports',
        'project_units',
        'today_logs',
        'page_visits',
        // Membership pivots: filtered out of normal queries via
        // wherePivotNull() on the belongsToMany relations.
        'organization_user',
        'project_members',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (!Schema::hasColumn($table->getTable(), 'deleted_at')) {
                    $table->softDeletes()->after('updated_at');
                }
            });
        }

        // Keep the common tenant lookups fast when the global SoftDeletes
        // scope filters on deleted_at.
        Schema::table('projects', function (Blueprint $table) {
            $table->index(['organization_id', 'deleted_at'], 'projects_org_deleted_idx');
        });
        Schema::table('attachments', function (Blueprint $table) {
            $table->index(['organization_id', 'deleted_at'], 'attachments_org_deleted_idx');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndexIfExists('projects_org_deleted_idx');
        });
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropIndexIfExists('attachments_org_deleted_idx');
        });

        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }
    }
};
