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
            $table->json('ingredients');
            $table->json('steps');
            $table->string('author_email');
            $table->string('slug')->unique();
            $table->timestamps();

            // Performance indexes
            $table->index(['author_email'], 'idx_author_email');
            $table->index(['slug'], 'idx_slug');
            $table->index(['created_at'], 'idx_created_at');

            // Full-text search indexes for name and description
            $table->fullText(['name', 'description'], 'idx_fulltext_search');
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
