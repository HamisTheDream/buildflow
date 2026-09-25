<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The frontend and dashboard expect the planned unit capacity on
     * `total_units`, while `units_count` is produced at runtime by
     * `withCount('units')` (tracked units). The original migration created
     * the physical column as `units_count`, which collided with the
     * withCount attribute and left `total_units` undefined everywhere.
     * Rename the physical column so both names are truthful.
     */
    public function up(): void
    {
        Schema::table('crm_properties', function (Blueprint $table) {
            $table->integer('total_units')->default(0)->after('status');
        });

        DB::statement('UPDATE crm_properties SET total_units = units_count');

        Schema::table('crm_properties', function (Blueprint $table) {
            $table->dropColumn('units_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_properties', function (Blueprint $table) {
            $table->integer('units_count')->default(0)->after('status');
        });

        DB::statement('UPDATE crm_properties SET units_count = total_units');

        Schema::table('crm_properties', function (Blueprint $table) {
            $table->dropColumn('total_units');
        });
    }
};
