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
        Schema::create('version_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "3000 Series", "DHW Standard"
            $table->string('prefix', 10)->nullable(); // e.g., "30", "40"
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->json('category_specs')->nullable(); // Category-specific specifications
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('version_categories');
    }
};
