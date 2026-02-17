<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ForumReply;
use App\Models\ForumTopic;

class ForumReplyNotification extends Notification
{
    use Queueable;

    public $reply;
    public $topic;

    public function __construct(ForumReply $reply, ForumTopic $topic)
    {
        $this->reply = $reply;
        $this->topic = $topic;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        // Generate URL based on whether it's a course forum or standalone forum
        if ($this->topic->forum->course) {
            $url = url("/courses/{$this->topic->forum->course->slug}/forums/{$this->topic->forum->id}/topics/{$this->topic->id}#reply-{$this->reply->id}");
        } else {
            $url = url("/forum/topics/{$this->topic->id}#reply-{$this->reply->id}");
        }

        return (new MailMessage)
            ->subject('New reply to your forum topic')
            ->greeting("Hello {$notifiable->name}!")
            ->line("Someone replied to your topic: {$this->topic->title}")
            ->line("Reply by: {$this->reply->user->name}")
            ->line("Content: " . substr($this->reply->content, 0, 100) . "...")
            ->action('View Reply', $url)
            ->line('Thank you for using our platform!');
    }

    public function toArray($notifiable)
    {
        // Generate action URL based on whether it's a course forum or standalone forum
        if ($this->topic->forum->course) {
            $actionUrl = "/courses/{$this->topic->forum->course->slug}/forums/{$this->topic->forum->id}/topics/{$this->topic->id}#reply-{$this->reply->id}";
        } else {
            $actionUrl = "/forum/topics/{$this->topic->id}#reply-{$this->reply->id}";
        }

        $data = [
            'type' => 'forum_reply',
            'title' => 'New reply to your topic',
            'message' => "{$this->reply->user->name} replied to your topic: {$this->topic->title}",
            'action_url' => $actionUrl,
            'data' => [
                'topic_id' => $this->topic->id,
                'reply_id' => $this->reply->id,
                'forum_id' => $this->topic->forum->id,
                'user_name' => $this->reply->user->name,
                'topic_title' => $this->topic->title,
            ],
        ];

        // Add course_id only if the forum belongs to a course
        if ($this->topic->forum->course) {
            $data['data']['course_id'] = $this->topic->forum->course->id;
        }

        return $data;
    }
}