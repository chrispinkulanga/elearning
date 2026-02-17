<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->text('bio')->nullable();
            $table->string('avatar')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();
            $table->string('nationality')->nullable();
            $table->string('location')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('website')->nullable();
            $table->json('social_links')->nullable(); // LinkedIn, Twitter, GitHub, etc.
            $table->json('languages')->nullable(); // Array of languages with proficiency levels
            $table->string('current_position')->nullable();
            $table->string('current_company')->nullable();
            $table->decimal('expected_salary', 10, 2)->nullable();
            $table->string('salary_currency', 3)->default('USD');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'freelance', 'internship'])->nullable();
            $table->enum('work_availability', ['immediately', '1_month', '3_months', '6_months', '1_year', 'not_available'])->nullable();
            $table->boolean('is_public')->default(true);
            $table->boolean('is_available_for_work')->default(false);
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();
            
            $table->index(['is_public', 'is_available_for_work']);
            $table->index('location');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_profiles');
    }
};