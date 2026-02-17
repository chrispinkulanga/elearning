<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name'); // Original filename
            $table->string('filename'); // Stored filename
            $table->string('path'); // File path
            $table->string('mime_type');
            $table->bigInteger('size'); // File size in bytes
            $table->enum('type', ['resume', 'cv', 'cover_letter', 'portfolio', 'certificate', 'other']);
            $table->string('title')->nullable(); // User-defined title
            $table->text('description')->nullable();
            $table->boolean('is_primary')->default(false); // Primary resume/CV
            $table->boolean('is_public')->default(false);
            $table->json('metadata')->nullable(); // Additional file metadata
            $table->timestamp('uploaded_at');
            $table->timestamps();
            
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'is_primary']);
            $table->index('is_public');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_documents');
    }
};