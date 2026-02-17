<?php
// Add this to app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use App\Models\Course;
use App\Models\Category;
use App\Models\Enrollment;
use App\Policies\CoursePolicy;
use App\Policies\CategoryPolicy;
use App\Observers\CourseObserver;
use App\Observers\EnrollmentObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Fix for "Specified key was too long" error in MySQL
        Schema::defaultStringLength(191);
        
        // Register policies
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        
        // Register model observers
        Course::observe(CourseObserver::class);
        Enrollment::observe(EnrollmentObserver::class);
    }
}