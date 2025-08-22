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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('slug')->unique();
            $table->string('image_url')->nullable(); // Optional: for recipe images
            $table->integer('prep_time')->nullable(); // Optional: in minutes
            $table->integer('cook_time')->nullable(); // Optional: in minutes
            $table->integer('servings')->nullable(); // Optional: number of servings
            $table->timestamps();

            // Indexes for performance
            $table->index('slug');
            $table->fullText(['name', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
