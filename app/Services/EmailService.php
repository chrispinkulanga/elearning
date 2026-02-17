<?php
// app/Services/EmailService.php
namespace App\Services;

use App\Mail\WelcomeEmail;
use App\Mail\CourseEnrollmentEmail;
use App\Mail\CertificateEmail;
use App\Mail\AnnouncementEmail;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Certificate;
use App\Models\Announcement;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    public function sendWelcomeEmail(User $user)
    {
        try {
            Mail::to($user->email)->send(new WelcomeEmail($user));
            
            Log::info('Welcome email sent', ['user_id' => $user->id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function sendEnrollmentEmail(User $user, Course $course, Enrollment $enrollment)
    {
        try {
            Mail::to($user->email)->send(new CourseEnrollmentEmail($user, $course, $enrollment));
            
            Log::info('Enrollment email sent', [
                'user_id' => $user->id,
                'course_id' => $course->id
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send enrollment email', [
                'user_id' => $user->id,
                'course_id' => $course->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function sendCertificateEmail(User $user, Course $course, Certificate $certificate)
    {
        try {
            Mail::to($user->email)->send(new CertificateEmail($user, $course, $certificate));
            
            Log::info('Certificate email sent', [
                'user_id' => $user->id,
                'certificate_id' => $certificate->id
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send certificate email', [
                'user_id' => $user->id,
                'certificate_id' => $certificate->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function sendAnnouncementEmail(User $user, Course $course, Announcement $announcement)
    {
        try {
            // Check user's notification preferences
            if (!$user->announcements_notifications ?? true) {
                return false; // User has disabled announcement emails
            }

            Mail::to($user->email)->send(new AnnouncementEmail($user, $course, $announcement));
            
            Log::info('Announcement email sent', [
                'user_id' => $user->id,
                'announcement_id' => $announcement->id
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send announcement email', [
                'user_id' => $user->id,
                'announcement_id' => $announcement->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function sendBulkAnnouncementEmails(Course $course, Announcement $announcement)
    {
        $enrolledUsers = $course->students()
            ->where('enrollments.status', 'active')
            ->where('announcements_notifications', true)
            ->get();

        $successCount = 0;
        $failCount = 0;

        foreach ($enrolledUsers as $user) {
            if ($this->sendAnnouncementEmail($user, $course, $announcement)) {
                $successCount++;
            } else {
                $failCount++;
            }

            // Add small delay to avoid overwhelming the mail server
            usleep(100000); // 0.1 second delay
        }

        Log::info('Bulk announcement emails sent', [
            'course_id' => $course->id,
            'announcement_id' => $announcement->id,
            'success_count' => $successCount,
            'fail_count' => $failCount
        ]);

        return [
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'total_attempted' => $successCount + $failCount
        ];
    }

    public function sendPasswordResetEmail(User $user, string $token)
    {
        try {
            Mail::send('emails.password-reset', [
                'user' => $user,
                'token' => $token,
                'resetUrl' => url('/reset-password?token=' . $token . '&email=' . urlencode($user->email))
            ], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Reset Your Password');
            });

            Log::info('Password reset email sent', ['user_id' => $user->id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function sendEmailVerification(User $user)
    {
        try {
            $verificationUrl = url('/email/verify/' . $user->id . '/' . sha1($user->email));

            Mail::send('emails.verify-email', [
                'user' => $user,
                'verificationUrl' => $verificationUrl
            ], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Verify Your Email Address');
            });

            Log::info('Email verification sent', ['user_id' => $user->id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send email verification', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function sendInstructorApplicationEmail(User $user, string $status, string $reason = '')
    {
        try {
            $subject = $status === 'approved' ? 'Instructor Application Approved' : 'Instructor Application Update';

            Mail::send('emails.instructor-application', [
                'user' => $user,
                'status' => $status,
                'reason' => $reason
            ], function ($message) use ($user, $subject) {
                $message->to($user->email)
                        ->subject($subject);
            });

            Log::info('Instructor application email sent', [
                'user_id' => $user->id,
                'status' => $status
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send instructor application email', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function getEmailStats(User $user = null)
    {
        // This would typically integrate with your email service provider's API
        // to get delivery stats, open rates, etc.
        
        return [
            'total_sent' => 0,
            'delivered' => 0,
            'opened' => 0,
            'clicked' => 0,
            'bounced' => 0,
            'unsubscribed' => 0,
        ];
    }
}
