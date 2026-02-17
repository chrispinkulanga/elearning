<?php
// app/Mail/AnnouncementEmail.php
namespace App\Mail;

use App\Models\User;
use App\Models\Course;
use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AnnouncementEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $course;
    public $announcement;

    public function __construct(User $user, Course $course, Announcement $announcement)
    {
        $this->user = $user;
        $this->course = $course;
        $this->announcement = $announcement;
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'New Announcement: ' . $this->announcement->title,
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.announcement',
            with: [
                'user' => $this->user,
                'course' => $this->course,
                'announcement' => $this->announcement,
                'courseUrl' => url('/courses/' . $this->course->slug),
                'unsubscribeUrl' => url('/unsubscribe-announcements'),
            ],
        );
    }

    public function attachments()
    {
        return [];
    }
}
