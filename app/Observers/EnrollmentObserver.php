<?php
// app/Observers/EnrollmentObserver.php
namespace App\Observers;

use App\Models\Enrollment;
use App\Notifications\NewEnrollment;
use App\Services\EmailService;
use App\Jobs\SendCertificate;
use App\Services\CertificateService;

class EnrollmentObserver
{
    protected $emailService;
    protected $certificateService;

    public function __construct(EmailService $emailService, CertificateService $certificateService)
    {
        $this->emailService = $emailService;
        $this->certificateService = $certificateService;
    }

    public function created(Enrollment $enrollment)
    {
        $enrollment->load(['user', 'course.instructor']);

        // Send enrollment email to student
        $this->emailService->sendEnrollmentEmail(
            $enrollment->user,
            $enrollment->course,
            $enrollment
        );

        // Notify instructor about new enrollment
        $enrollment->course->instructor->notify(
            new NewEnrollment($enrollment->user, $enrollment->course, $enrollment)
        );

        \Log::info('New enrollment created', [
            'enrollment_id' => $enrollment->id,
            'user_id' => $enrollment->user_id,
            'course_id' => $enrollment->course_id,
            'amount_paid' => $enrollment->amount_paid
        ]);
    }

    public function updated(Enrollment $enrollment)
    {
        // Check if progress reached 100% (course completed)
        if ($enrollment->isDirty('progress_percentage') && $enrollment->progress_percentage == 100) {
            $this->handleCourseCompletion($enrollment);
        }

        // Check if status changed to cancelled
        if ($enrollment->isDirty('status') && $enrollment->status === 'cancelled') {
            \Log::info('Enrollment cancelled', [
                'enrollment_id' => $enrollment->id,
                'user_id' => $enrollment->user_id,
                'course_id' => $enrollment->course_id
            ]);
        }
    }

    protected function handleCourseCompletion(Enrollment $enrollment)
    {
        $enrollment->load(['user', 'course']);

        try {
            // Generate certificate
            $certificate = $this->certificateService->generateCertificate(
                $enrollment->user,
                $enrollment->course
            );

            // Queue certificate email
            SendCertificate::dispatch(
                $enrollment->user,
                $enrollment->course,
                $certificate
            );

            \Log::info('Course completed and certificate generated', [
                'enrollment_id' => $enrollment->id,
                'user_id' => $enrollment->user_id,
                'course_id' => $enrollment->course_id,
                'certificate_id' => $certificate->id
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to generate certificate on course completion', [
                'enrollment_id' => $enrollment->id,
                'user_id' => $enrollment->user_id,
                'course_id' => $enrollment->course_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function deleted(Enrollment $enrollment)
    {
        \Log::info('Enrollment deleted', [
            'enrollment_id' => $enrollment->id,
            'user_id' => $enrollment->user_id,
            'course_id' => $enrollment->course_id
        ]);
    }
}
