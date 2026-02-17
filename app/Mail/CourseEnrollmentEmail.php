<?php
// app/Mail/CourseEnrollmentEmail.php
namespace App\Mail;

use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CourseEnrollmentEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $course;
    public $enrollment;

    public function __construct(User $user, Course $course, Enrollment $enrollment)
    {
        $this->user = $user;
        $this->course = $course;
        $this->enrollment = $enrollment;
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'Successfully Enrolled in ' . $this->course->title,
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.course-enrollment',
            with: [
                'user' => $this->user,
                'course' => $this->course,
                'enrollment' => $this->enrollment,
                'courseUrl' => url('/courses/' . $this->course->slug),
                'dashboardUrl' => url('/dashboard'),
            ],
        );
    }

    public function attachments()
    {
        return [];
    }
}
