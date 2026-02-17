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
        Schema::table('courses', function (Blueprint $table) {
            // Add average_rating column if it doesn't exist
            if (!Schema::hasColumn('courses', 'average_rating')) {
                $table->decimal('average_rating', 3, 2)->default(0)->after('category_id');
            }
            
            // Add is_published column if it doesn't exist
            if (!Schema::hasColumn('courses', 'is_published')) {
                $table->boolean('is_published')->default(false)->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'average_rating')) {
                $table->dropColumn('average_rating');
            }
            
            if (Schema::hasColumn('courses', 'is_published')) {
                $table->dropColumn('is_published');
            }
        });
    }
};
