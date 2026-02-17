<?php
// database/migrations/2024_01_01_000013_create_forum_topics_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('forum_topics', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('category')->default('general');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('forum_id')->constrained('forums')->onDelete('cascade');
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_draft')->default(false); // Add draft status
            $table->integer('views')->default(0);
            $table->json('tags')->nullable();
            $table->integer('reports_count')->default(0);
            $table->integer('likes_count')->default(0);
            
            // Poll fields
            $table->string('poll_question')->nullable();
            $table->json('poll_options')->nullable();
            $table->boolean('poll_is_multiple_choice')->default(false);
            $table->boolean('poll_is_active')->default(true);
            $table->timestamp('poll_expires_at')->nullable();
            
            // Attachment fields
            $table->json('attachments')->nullable();
            $table->integer('attachments_count')->default(0);
            
            $table->timestamps();
        });

        // Create likes table for polymorphic relationships
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('likeable_type'); // For polymorphic relationship
            $table->unsignedBigInteger('likeable_id'); // For polymorphic relationship
            $table->timestamps();
            
            // Add indexes for performance
            $table->index(['likeable_type', 'likeable_id']);
            $table->index('user_id');
            
            // Ensure a user can only like something once
            $table->unique(['user_id', 'likeable_type', 'likeable_id'], 'unique_user_like');
        });
    }

    public function down()
    {
        Schema::dropIfExists('likes');
        Schema::dropIfExists('forum_topics');
    }
};