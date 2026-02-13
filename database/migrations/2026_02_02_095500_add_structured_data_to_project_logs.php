<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_logs', function (Blueprint $table) {
            $table->integer('workforce_count')->nullable()->after('type'); // number of people on site
            $table->string('weather')->nullable()->after('workforce_count'); // e.g. Sunny, Rain
            $table->text('materials_delivered')->nullable()->after('body'); // Structured material logs
            $table->text('blockers')->nullable()->after('materials_delivered'); // Issues/Delays
            $table->text('next_day_plan')->nullable()->after('blockers'); // Continuity
        });
    }

    public function down(): void
    {
        Schema::table('project_logs', function (Blueprint $table) {
            $table->dropColumn(['workforce_count', 'weather', 'materials_delivered', 'blockers', 'next_day_plan']);
        });
    }
};
