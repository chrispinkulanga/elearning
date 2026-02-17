<?php
// database/seeders/DatabaseSeeder.php (Complete Version)
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');

        // Disable foreign key constraints during seeding
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // Run seeders in correct order (respecting dependencies)
            $this->call([
                RoleSeeder::class,      // First: Create roles and permissions
                UserSeeder::class,      // Second: Create users and assign roles
            ]);

        } finally {
            // Re-enable foreign key constraints
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $this->displaySeedingSummary();
    }

    /**
     * Display a summary of what was seeded.
     */
    private function displaySeedingSummary(): void
    {
        $this->command->info('');
        $this->command->info('🎉 Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->table(
            ['Model', 'Count'],
            [
                ['Users', \App\Models\User::count()],
                ['Categories', \App\Models\Category::count()],
                ['Courses', \App\Models\Course::count()],
                ['Lessons', \App\Models\Lesson::count()],
                ['Quizzes', \App\Models\Quiz::count()],
                ['Quiz Questions', \App\Models\QuizQuestion::count()],
                ['Enrollments', \App\Models\Enrollment::count()],
                ['Reviews', \App\Models\Review::count()],
                ['Forums', \App\Models\Forum::count()],
                ['Student Products', \App\Models\StudentProduct::count()],
                ['Roles', \Spatie\Permission\Models\Role::count()],
                ['Permissions', \Spatie\Permission\Models\Permission::count()],
            ]
        );

        $this->command->info('');
        $this->command->info('🔑 Default Login Credentials:');
        $this->command->info('Admin: africanchildproject@gmail.com / african123');
        $this->command->info('');
        $this->command->info('🚀 Your e-learning platform is ready to use!');
    }
}
