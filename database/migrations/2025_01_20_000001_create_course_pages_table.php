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
        Schema::create('course_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('order_index')->default(0);
            $table->enum('content_type', ['lesson', 'quiz', 'assignment', 'overview'])->default('lesson');
            $table->boolean('is_published')->default(false);
            $table->boolean('is_preview')->default(false);
            $table->json('settings')->nullable(); // For page-specific settings
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better performance
            $table->index(['course_id', 'order_index']);
            $table->index(['course_id', 'is_published']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_pages');
    }
};
