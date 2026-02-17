<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Added this import for DB facade

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove duplicate web guard roles (keep only sanctum ones)
        // The guard_name is only stored in the roles table, not in model_has_roles
        DB::table('roles')->where('guard_name', 'web')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the web guard roles that were deleted
        
        // Recreate admin role with web guard
        DB::table('roles')->insert([
            'name' => 'admin',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Recreate instructor role with web guard
        DB::table('roles')->insert([
            'name' => 'instructor',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Recreate student role with web guard
        DB::table('roles')->insert([
            'name' => 'student',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
