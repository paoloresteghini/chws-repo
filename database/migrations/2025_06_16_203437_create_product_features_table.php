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
        Schema::create('product_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('feature_key'); // e.g., 'dhw_metrics', 'vessel_support'
            $table->string('feature_name'); // Human readable name
            $table->json('feature_config')->nullable(); // Configuration for the feature
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'feature_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_features');
    }
};
