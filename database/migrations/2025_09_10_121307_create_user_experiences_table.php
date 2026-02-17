<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('company_name');
            $table->string('position');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable(); // null for current position
            $table->boolean('is_current')->default(false);
            $table->string('employment_type')->nullable(); // full_time, part_time, contract, etc.
            $table->json('achievements')->nullable(); // Array of achievements
            $table->json('skills_used')->nullable(); // Array of skills used in this role
            $table->string('company_website')->nullable();
            $table->string('company_logo')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_current']);
            $table->index('company_name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_experiences');
    }
};