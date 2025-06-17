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
        Schema::create('temperature_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Make name unique instead of the complex constraint
            $table->decimal('primary_flow_temp', 5, 2);
            $table->decimal('primary_return_temp', 5, 2);
            $table->decimal('secondary_flow_temp', 5, 2);
            $table->decimal('secondary_return_temp', 5, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['primary_flow_temp', 'primary_return_temp', 'secondary_flow_temp', 'secondary_return_temp'], 'temp_profile_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('temperature_profiles');
    }
};
