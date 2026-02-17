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
        Schema::create('alumnis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('graduation_year')->nullable();
            $table->string('degree')->nullable();
            $table->string('major')->nullable();
            $table->text('achievements')->nullable();
            $table->string('current_position')->nullable();
            $table->string('company')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('status')->default('active'); // active, inactive, pending
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumnis');
    }
};
