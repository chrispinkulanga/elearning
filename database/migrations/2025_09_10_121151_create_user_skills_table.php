<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name'); // Skill name
            $table->enum('category', ['technical', 'soft', 'language', 'certification', 'other']);
            $table->enum('proficiency_level', ['beginner', 'intermediate', 'advanced', 'expert']);
            $table->integer('years_of_experience')->nullable();
            $table->text('description')->nullable();
            $table->json('endorsements')->nullable(); // Array of user endorsements
            $table->boolean('is_verified')->default(false);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'category']);
            $table->index(['user_id', 'proficiency_level']);
            $table->unique(['user_id', 'name']); // Prevent duplicate skills
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_skills');
    }
};