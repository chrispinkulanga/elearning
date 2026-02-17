<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('institution_name');
            $table->string('degree');
            $table->string('field_of_study')->nullable();
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable(); // null for current education
            $table->boolean('is_current')->default(false);
            $table->decimal('gpa', 3, 2)->nullable();
            $table->string('gpa_scale', 10)->nullable(); // 4.0, 5.0, 100, etc.
            $table->json('activities')->nullable(); // Extracurricular activities
            $table->json('achievements')->nullable(); // Academic achievements
            $table->string('institution_website')->nullable();
            $table->string('institution_logo')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_current']);
            $table->index('institution_name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_educations');
    }
};