<?php
// database/seeders/UserSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update admin user
        $admin = User::updateOrCreate(
            ['email' => 'africanchildproject@gmail.com'],
            [
                'name' => 'African Child Project Admin',
                'password' => Hash::make('african123'),
                'phone' => '+255123456789',
                'bio' => 'System administrator for African Child Project e-learning platform.',
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $this->command->info('Admin user ready!');
        $this->command->info('Admin: africanchildproject@gmail.com / african123');
        $this->command->info('Total users: ' . User::count());
    }
}