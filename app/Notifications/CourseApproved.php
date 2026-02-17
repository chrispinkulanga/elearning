<?php
// app/Notifications/CourseApproved.php
namespace App\Notifications;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class CourseApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public $course;

    public function __construct(Course $course)
    {
        $this->course = $course;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Course Has Been Approved!')
            ->greeting('Congratulations!')
            ->line('Your course "' . $this->course->title . '" has been approved and is now live.')
            ->line('Students can now enroll in your course.')
            ->action('View Course', url('/courses/' . $this->course->slug))
            ->line('Thank you for contributing to our learning community!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Course Approved',
            'message' => 'Your course "' . $this->course->title . '" has been approved and is now live.',
            'course_id' => $this->course->id,
            'course_title' => $this->course->title,
            'action_url' => url('/courses/' . $this->course->slug),
            'type' => 'course_approved'
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'course_id' => $this->course->id,
            'course_title' => $this->course->title,
        ];
    }
}
