<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CreateRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create default roles for the application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating roles...');

        $roles = ['student', 'instructor', 'admin'];

        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $this->info("Role '$roleName' created/found successfully");
        }

        $this->info('✅ All roles created successfully!');
    }
}