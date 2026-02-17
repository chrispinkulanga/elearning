<?php
// app/Notifications/QuizCompleted.php
namespace App\Notifications;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuizCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public $quiz;
    public $attempt;
    public $course;

    public function __construct(Quiz $quiz, QuizAttempt $attempt, Course $course)
    {
        $this->quiz = $quiz;
        $this->attempt = $attempt;
        $this->course = $course;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $status = $this->attempt->passed ? 'Passed' : 'Failed';
        $subject = 'Quiz ' . $status . ': ' . $this->quiz->title;

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have completed the quiz "' . $this->quiz->title . '" in course "' . $this->course->title . '".')
            ->line('Your Score: ' . $this->attempt->score . '%')
            ->line('Status: ' . $status)
            ->line('Correct Answers: ' . $this->attempt->correct_answers . ' out of ' . $this->attempt->total_questions)
            ->action('View Results', url('/courses/' . $this->course->slug . '/quizzes/' . $this->quiz->id . '/attempts/' . $this->attempt->id . '/results'))
            ->line($this->attempt->passed ? 'Congratulations on passing!' : 'Keep studying and try again!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Quiz Completed',
            'message' => 'You completed "' . $this->quiz->title . '" with a score of ' . $this->attempt->score . '%',
            'quiz_id' => $this->quiz->id,
            'quiz_title' => $this->quiz->title,
            'course_id' => $this->course->id,
            'course_title' => $this->course->title,
            'attempt_id' => $this->attempt->id,
            'score' => $this->attempt->score,
            'passed' => $this->attempt->passed,
            'action_url' => url('/courses/' . $this->course->slug . '/quizzes/' . $this->quiz->id),
            'type' => 'quiz_completed'
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'quiz_id' => $this->quiz->id,
            'attempt_id' => $this->attempt->id,
            'score' => $this->attempt->score,
            'passed' => $this->attempt->passed,
        ];
    }
}