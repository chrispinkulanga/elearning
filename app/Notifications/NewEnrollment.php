<?php
// app/Notifications/NewEnrollment.php
namespace App\Notifications;

use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewEnrollment extends Notification implements ShouldQueue
{
    use Queueable;

    public $student;
    public $course;
    public $enrollment;

    public function __construct(User $student, Course $course, Enrollment $enrollment)
    {
        $this->student = $student;
        $this->course = $course;
        $this->enrollment = $enrollment;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Student Enrolled in Your Course')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new student has enrolled in your course "' . $this->course->title . '".')
            ->line('Student: ' . $this->student->name)
            ->line('Enrollment Date: ' . $this->enrollment->enrolled_at->format('F d, Y'))
            ->action('View Course Analytics', url('/instructor/courses/' . $this->course->id . '/analytics'))
            ->line('Keep up the great work!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'New Enrollment',
            'message' => $this->student->name . ' enrolled in your course "' . $this->course->title . '".',
            'student_id' => $this->student->id,
            'student_name' => $this->student->name,
            'course_id' => $this->course->id,
            'course_title' => $this->course->title,
            'enrollment_id' => $this->enrollment->id,
            'action_url' => url('/instructor/courses/' . $this->course->id),
            'type' => 'new_enrollment'
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'student_id' => $this->student->id,
            'course_id' => $this->course->id,
            'enrollment_id' => $this->enrollment->id,
        ];
    }
}