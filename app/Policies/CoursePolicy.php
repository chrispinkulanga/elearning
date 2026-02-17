<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CoursePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Allow users to view courses if they're logged in
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Course $course): bool
    {
        // Allow viewing if course is published or user is instructor/admin
        return $course->status === 'approved' || 
               $user->hasRole(['admin', 'instructor']) || 
               $course->instructor_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Allow instructors and admins to create courses
        return $user->hasRole(['admin', 'instructor']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Course $course): bool
    {
        // Allow if user is admin or course instructor
        return $user->hasRole('admin') || $course->instructor_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Course $course): bool
    {
        // Allow if user is admin or course instructor
        return $user->hasRole('admin') || $course->instructor_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Course $course): bool
    {
        // Allow if user is admin or course instructor
        return $user->hasRole('admin') || $course->instructor_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Course $course): bool
    {
        // Allow if user is admin or course instructor
        return $user->hasRole('admin') || $course->instructor_id === $user->id;
    }
}
