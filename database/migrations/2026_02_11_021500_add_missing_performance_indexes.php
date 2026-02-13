<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing performance indexes identified during code review.
     * Safely skips indexes that already exist.
     */
    public function up(): void
    {
        $this->addIndexIfNotExists('payments', 'reference', 'payments_reference_index');
        $this->addCompositeIndexIfNotExists('project_logs', ['project_id', 'log_date'], 'project_logs_project_id_log_date_index');
        $this->addCompositeIndexIfNotExists('project_costs', ['project_id', 'cost_date'], 'project_costs_project_id_cost_date_index');
        $this->addCompositeIndexIfNotExists('today_logs', ['project_id', 'log_date'], 'today_logs_project_id_log_date_index');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('payments', 'payments_reference_index');
        $this->dropIndexIfExists('project_logs', 'project_logs_project_id_log_date_index');
        $this->dropIndexIfExists('project_costs', 'project_costs_project_id_cost_date_index');
        $this->dropIndexIfExists('today_logs', 'today_logs_project_id_log_date_index');
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    private function addIndexIfNotExists(string $table, string $column, string $indexName): void
    {
        if (!$this->indexExists($table, $indexName)) {
            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->index($column);
            });
        }
    }

    private function addCompositeIndexIfNotExists(string $table, array $columns, string $indexName): void
    {
        if (!$this->indexExists($table, $indexName)) {
            Schema::table($table, function (Blueprint $t) use ($columns) {
                $t->index($columns);
            });
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if ($this->indexExists($table, $indexName)) {
            Schema::table($table, function (Blueprint $t) use ($indexName) {
                $t->dropIndex($indexName);
            });
        }
    }
};
