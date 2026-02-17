<?php
// app/Jobs/SendCertificate.php
namespace App\Jobs;

use App\Models\User;
use App\Models\Course;
use App\Models\Certificate;
use App\Mail\CertificateEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendCertificate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $course;
    public $certificate;

    public function __construct(User $user, Course $course, Certificate $certificate)
    {
        $this->user = $user;
        $this->course = $course;
        $this->certificate = $certificate;
    }

    public function handle()
    {
        // Send certificate email with PDF attachment
        Mail::to($this->user->email)->send(
            new CertificateEmail($this->user, $this->course, $this->certificate)
        );

        // Update certificate as sent
        $this->certificate->update([
            'email_sent_at' => now()
        ]);
    }

    public function failed(\Exception $exception)
    {
        // Handle failed job
        \Log::error('Failed to send certificate email', [
            'user_id' => $this->user->id,
            'certificate_id' => $this->certificate->id,
            'error' => $exception->getMessage()
        ]);
    }
}
