<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create Units Table
        Schema::create('project_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();

            $table->string('name'); // e.g. "Plot 4", "Block B"
            $table->string('type')->default('unit'); // e.g. "Duplex", "Flat", "Common Area"
            $table->string('status')->default('active'); // active, completed, sold
            $table->text('description')->nullable();

            $table->timestamps();

            $table->unique(['project_id', 'name']); // Prevent Duplicate names in a project
        });

        // 2. Add Foreign Keys to core tables
        $tables = ['project_logs', 'project_tasks', 'project_issues', 'project_costs'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('project_unit_id')->nullable()->after('project_id')->constrained()->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        $tables = ['project_logs', 'project_tasks', 'project_issues', 'project_costs'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['project_unit_id']);
                $table->dropColumn('project_unit_id');
            });
        }

        Schema::dropIfExists('project_units');
    }
};
