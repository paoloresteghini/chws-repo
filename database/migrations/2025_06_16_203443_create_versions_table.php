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
        Schema::create('versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('model_number'); // Can be numeric (3017) or alphanumeric (30/120)
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('status')->default(true);
            $table->foreignId('category_id')->nullable()->constrained('version_categories')->onDelete('set null');

            // Optional vessel/capacity support
            $table->boolean('has_vessel_options')->default(false);
            $table->json('specifications')->nullable(); // For product-specific specs

            $table->timestamps();

            // Ensure unique model per product
            $table->unique(['product_id', 'model_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('versions');
    }
};
