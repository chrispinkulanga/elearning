<?php
// =====================================
// COMPLETE SEEDERS
// =====================================

// database/seeders/RoleSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles for web guard
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $instructorRole = Role::firstOrCreate(['name' => 'instructor', 'guard_name' => 'web']);
        $studentRole = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        // Create permissions for web guard
        $permissions = [
            // User management
            'manage_users',
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'ban_users',
            
            // Course management
            'manage_courses',
            'view_all_courses',
            'create_courses',
            'edit_own_courses',
            'edit_all_courses',
            'delete_own_courses',
            'delete_all_courses',
            'approve_courses',
            'reject_courses',
            
            // Category management
            'manage_categories',
            'create_categories',
            'edit_categories',
            'delete_categories',
            
            // Content moderation
            'moderate_content',
            'moderate_reviews',
            'moderate_forums',
            'moderate_student_products',
            
            // Reporting and analytics
            'view_reports',
            'view_analytics',
            'view_system_stats',
            'export_data',
            
            // Instructor specific
            'view_course_analytics',
            'manage_own_courses',
            'view_student_progress',
            'create_announcements',
            'manage_course_content',
            'view_earnings',
            
            // Student specific
            'enroll_courses',
            'access_enrolled_courses',
            'submit_assignments',
            'take_quizzes',
            'participate_forums',
            'submit_reviews',
            'view_certificates',
            'download_resources',
            'submit_student_products',
            
            // Payment and financial
            'process_payments',
            'issue_refunds',
            'view_financial_reports',
            
            // System settings
            'manage_settings',
            'manage_integrations',
            'view_logs',
            'backup_system',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assign permissions to roles
        
        // Admin gets all permissions
        $adminRole->givePermissionTo(Permission::where('guard_name', 'web')->get());
        
        // Instructor permissions
        $instructorRole->givePermissionTo([
            'create_courses',
            'edit_own_courses',
            'delete_own_courses',
            'manage_own_courses',
            'view_course_analytics',
            'view_student_progress',
            'create_announcements',
            'manage_course_content',
            'view_earnings',
            'participate_forums',
            'view_certificates',
        ]);

        // Student permissions
        $studentRole->givePermissionTo([
            'enroll_courses',
            'access_enrolled_courses',
            'submit_assignments',
            'take_quizzes',
            'participate_forums',
            'submit_reviews',
            'view_certificates',
            'download_resources',
            'submit_student_products',
        ]);

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('Created roles: Admin, Instructor, Student');
        $this->command->info('Created ' . count($permissions) . ' permissions');
    }
}