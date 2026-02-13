<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Performance indexes for common queries.
     * This migration is idempotent - safe to run multiple times.
     */
    public function up(): void
    {
        // Project members - frequently joined by project_id
        $this->addIndexIfNotExists('project_members', 'project_members_project_id_user_id_index', ['project_id', 'user_id']);

        // Project tasks - filtered by status and assigned_to
        $this->addIndexIfNotExists('project_tasks', 'project_tasks_project_id_status_index', ['project_id', 'status']);
        $this->addIndexIfNotExists('project_tasks', 'project_tasks_assigned_to_index', ['assigned_to']);
        $this->addIndexIfNotExists('project_tasks', 'project_tasks_project_id_due_date_index', ['project_id', 'due_date']);

        // Project costs - filtered by status
        $this->addIndexIfNotExists('project_costs', 'project_costs_project_id_status_index', ['project_id', 'status']);
        $this->addIndexIfNotExists('project_costs', 'project_costs_project_id_created_at_index', ['project_id', 'created_at']);

        // Project issues - filtered by status
        $this->addIndexIfNotExists('project_issues', 'project_issues_project_id_status_index', ['project_id', 'status']);
        $this->addIndexIfNotExists('project_issues', 'project_issues_assigned_to_index', ['assigned_to']);

        // Activity logs - filtered by project and ordered by date
        $this->addIndexIfNotExists('activity_logs', 'activity_logs_project_id_created_at_index', ['project_id', 'created_at']);
        $this->addIndexIfNotExists('activity_logs', 'activity_logs_organization_id_created_at_index', ['organization_id', 'created_at']);

        // Project logs - filtered by date
        $this->addIndexIfNotExists('project_logs', 'project_logs_project_id_log_date_index', ['project_id', 'log_date']);

        // Projects - filtered by organization and status
        $this->addIndexIfNotExists('projects', 'projects_organization_id_status_index', ['organization_id', 'status']);
    }

    /**
     * Add an index if it doesn't already exist.
     */
    private function addIndexIfNotExists(string $table, string $indexName, array $columns): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $exists = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);

        if (empty($exists)) {
            Schema::table($table, function (Blueprint $table) use ($columns) {
                $table->index($columns);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexIfExists('project_members', 'project_members_project_id_user_id_index');
        $this->dropIndexIfExists('project_tasks', 'project_tasks_project_id_status_index');
        $this->dropIndexIfExists('project_tasks', 'project_tasks_assigned_to_index');
        $this->dropIndexIfExists('project_tasks', 'project_tasks_project_id_due_date_index');
        $this->dropIndexIfExists('project_costs', 'project_costs_project_id_status_index');
        $this->dropIndexIfExists('project_costs', 'project_costs_project_id_created_at_index');
        $this->dropIndexIfExists('project_issues', 'project_issues_project_id_status_index');
        $this->dropIndexIfExists('project_issues', 'project_issues_assigned_to_index');
        $this->dropIndexIfExists('activity_logs', 'activity_logs_project_id_created_at_index');
        $this->dropIndexIfExists('activity_logs', 'activity_logs_organization_id_created_at_index');
        $this->dropIndexIfExists('project_logs', 'project_logs_project_id_log_date_index');
        $this->dropIndexIfExists('projects', 'projects_organization_id_status_index');
    }

    /**
     * Drop an index if it exists.
     */
    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $exists = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);

        if (!empty($exists)) {
            Schema::table($table, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        }
    }
};
