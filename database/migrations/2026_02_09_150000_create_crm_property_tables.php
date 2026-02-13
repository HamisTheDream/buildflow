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
        Schema::create('crm_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('type')->default('residential'); // residential, commercial, industrial, land
            $table->string('status')->default('active'); // active, sold, maintenance
            $table->integer('units_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('crm_property_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('crm_properties')->cascadeOnDelete();
            $table->string('unit_number');
            $table->string('type')->default('apartment'); // apartment, villa, shop, office, plot
            $table->string('status')->default('vacant'); // vacant, occupied, maintenance, reserved
            $table->integer('bedrooms')->nullable();
            $table->float('bathrooms')->nullable();
            $table->float('size_sqm')->nullable();
            $table->bigInteger('rent_amount_cents')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_property_units');
        Schema::dropIfExists('crm_properties');
    }
};
