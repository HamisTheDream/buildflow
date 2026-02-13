<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('platform_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('manager_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('id')->constrained('platform_departments')->nullOnDelete();
            $table->string('job_title')->nullable()->after('email');
            $table->string('role_type')->default('admin')->after('is_super'); // admin, content_developer, sales_rep
            $table->string('status')->default('active')->after('is_active'); // active, suspended, on_leave
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['department_id', 'job_title', 'role_type', 'status']);
        });

        Schema::dropIfExists('platform_departments');
    }
};
