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
        Schema::create('vessel_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "1000L", "1500L" or "196L Capacity"
            $table->decimal('capacity', 10, 2)->nullable(); // Vessel capacity in liters
            $table->string('capacity_unit', 10)->default('L'); // L, kL, etc.
            $table->text('description')->nullable();
            $table->json('specifications')->nullable(); // Additional vessel specs
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vessel_configurations');
    }
};
