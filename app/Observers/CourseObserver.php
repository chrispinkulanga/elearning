<?php
// app/Observers/CourseObserver.php
namespace App\Observers;

use App\Models\Course;
use App\Models\Forum;
use App\Notifications\CourseApproved;
use App\Services\EmailService;

class CourseObserver
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function created(Course $course)
    {
        // Create default forum for the course
        Forum::create([
            'title' => 'General Discussion',
            'description' => 'General discussion forum for ' . $course->title,
            'course_id' => $course->id,
            'is_active' => true,
        ]);

        // Log course creation
        \Log::info('Course created', [
            'course_id' => $course->id,
            'title' => $course->title,
            'instructor_id' => $course->instructor_id
        ]);
    }

    public function updated(Course $course)
    {
        // Check if status changed to approved
        if ($course->isDirty('status') && $course->status === 'approved') {
            // Notify instructor
            $course->instructor->notify(new CourseApproved($course));

            \Log::info('Course approved', [
                'course_id' => $course->id,
                'title' => $course->title,
                'instructor_id' => $course->instructor_id
            ]);
        }

        // Check if status changed to rejected
        if ($course->isDirty('status') && $course->status === 'rejected') {
            // Send rejection notification
            // You can create a CourseRejected notification class

            \Log::info('Course rejected', [
                'course_id' => $course->id,
                'title' => $course->title,
                'instructor_id' => $course->instructor_id,
                'reason' => $course->rejection_reason ?? 'No reason provided'
            ]);
        }
    }

    public function deleting(Course $course)
    {
        // Clean up related data before deletion
        $course->lessons()->delete();
        $course->quizzes()->delete();
        $course->forums()->delete();
        $course->reviews()->delete();
        $course->announcements()->delete();
        $course->studentProducts()->delete();

        // Update enrollments to cancelled
        $course->enrollments()->update(['status' => 'cancelled']);

        \Log::info('Course deleted with cleanup', [
            'course_id' => $course->id,
            'title' => $course->title
        ]);
    }

    public function deleted(Course $course)
    {
        // Clean up files
        if ($course->thumbnail) {
            \Storage::disk('public')->delete($course->thumbnail);
        }

        \Log::info('Course deletion completed', [
            'course_id' => $course->id,
            'title' => $course->title
        ]);
    }
}
