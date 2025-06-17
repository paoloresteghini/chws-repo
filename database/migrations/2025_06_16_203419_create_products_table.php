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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('type')->default('heat_exchanger'); // heat_exchanger, dhw_system, etc.
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('has_temperature_profiles')->default(true);
            $table->boolean('has_vessel_options')->default(false);
            $table->json('product_specific_fields')->nullable(); // For custom fields per product type
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
