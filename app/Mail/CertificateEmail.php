<?php
// app/Mail/CertificateEmail.php
namespace App\Mail;

use App\Models\User;
use App\Models\Course;
use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CertificateEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $course;
    public $certificate;

    public function __construct(User $user, Course $course, Certificate $certificate)
    {
        $this->user = $user;
        $this->course = $course;
        $this->certificate = $certificate;
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'Congratulations! Your Certificate is Ready',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.certificate',
            with: [
                'user' => $this->user,
                'course' => $this->course,
                'certificate' => $this->certificate,
                'certificateUrl' => url('/certificates/' . $this->certificate->id),
                'verificationUrl' => url('/verify-certificate/' . $this->certificate->certificate_id),
            ],
        );
    }

    public function attachments()
    {
        $attachments = [];
        
        if ($this->certificate->certificate_url && Storage::disk('public')->exists($this->certificate->certificate_url)) {
            $attachments[] = Attachment::fromStorageDisk('public', $this->certificate->certificate_url)
                ->as('Certificate_' . $this->certificate->certificate_id . '.pdf')
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}