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
        Schema::create('performance_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained()->onDelete('cascade');
            $table->foreignId('temperature_profile_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('vessel_configuration_id')->nullable()->constrained()->onDelete('cascade');

            // Common performance metrics
            $table->decimal('heat_input_kw', 8, 2);
            $table->decimal('primary_flow_rate_ls', 8, 4);
            $table->decimal('secondary_flow_rate_ls', 8, 4);
            $table->decimal('pressure_drop_kpa', 8, 2);

            // DHW-specific metrics (for Aquafast)
            $table->decimal('first_hour_dhw_supply', 8, 2)->nullable();
            $table->decimal('subsequent_hour_dhw_supply', 8, 2)->nullable();

            // Additional product-specific metrics
            $table->json('additional_metrics')->nullable();

            $table->timestamps();

            // Custom named index instead of auto-generated long name
            $table->index(['version_id', 'temperature_profile_id', 'vessel_configuration_id'], 'perf_data_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_data');
    }
};
