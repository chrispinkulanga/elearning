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
        Schema::table('categories', function (Blueprint $table) {
            // Check if color column already exists before adding it
            if (!Schema::hasColumn('categories', 'color')) {
                $table->string('color')->nullable()->after('image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Only drop if column exists
            if (Schema::hasColumn('categories', 'color')) {
                $table->dropColumn('color');
            }
        });
    }
};
