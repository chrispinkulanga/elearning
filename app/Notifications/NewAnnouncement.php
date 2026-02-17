<?php
// app/Notifications/NewAnnouncement.php
namespace App\Notifications;

use App\Models\Announcement;
use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAnnouncement extends Notification implements ShouldQueue
{
    use Queueable;

    public $announcement;
    public $course;

    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
        $this->course = $announcement->course;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Announcement: ' . $this->announcement->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('There is a new announcement in your course "' . $this->course->title . '".')
            ->line('**' . $this->announcement->title . '**')
            ->line(substr($this->announcement->content, 0, 200) . '...')
            ->action('Read Full Announcement', url('/courses/' . $this->course->slug . '/announcements/' . $this->announcement->id))
            ->line('Stay updated with your course progress!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'New Announcement',
            'message' => 'New announcement in "' . $this->course->title . '": ' . $this->announcement->title,
            'announcement_id' => $this->announcement->id,
            'announcement_title' => $this->announcement->title,
            'course_id' => $this->course->id,
            'course_title' => $this->course->title,
            'action_url' => url('/courses/' . $this->course->slug . '/announcements'),
            'type' => 'new_announcement'
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'announcement_id' => $this->announcement->id,
            'course_id' => $this->course->id,
        ];
    }
}
