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
        Schema::create('course_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('course_pages')->onDelete('cascade');
            $table->string('widget_type'); // text, image, video, mcq, poll, file, code, embed
            $table->json('widget_data'); // Flexible data storage for different widget types
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // Widget-specific settings
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['page_id', 'order_index']);
            $table->index(['page_id', 'widget_type']);
            $table->index(['page_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_widgets');
    }
};
