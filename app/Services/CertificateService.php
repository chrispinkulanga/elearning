<?php
// app/Services/CertificateService.php
namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use PDF;

class CertificateService
{
    public function generateCertificate(User $user, Course $course)
    {
        // Check if user completed the course
        $enrollment = $user->enrollments()
            ->where('course_id', $course->id)
            ->where('progress_percentage', 100)
            ->first();

        if (!$enrollment) {
            throw new \Exception('Course not completed');
        }

        // Check if certificate already exists
        $existingCertificate = Certificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existingCertificate) {
            return $existingCertificate;
        }

        // Generate unique certificate ID
        $certificateId = $this->generateCertificateId();

        // Create certificate record
        $certificate = Certificate::create([
            'certificate_id' => $certificateId,
            'user_id' => $user->id,
            'course_id' => $course->id,
            'issued_at' => now(),
        ]);

        // Generate PDF
        $pdfPath = $this->generateCertificatePDF($certificate);
        $certificate->update(['certificate_url' => $pdfPath]);

        return $certificate;
    }

    public function generateCertificatePDF(Certificate $certificate)
    {
        $certificate->load(['user', 'course.instructor']);
        
        $templateData = [
            'certificate' => $certificate,
            'student_name' => $certificate->user->name,
            'course_title' => $certificate->course->title,
            'instructor_name' => $certificate->course->instructor->name,
            'completion_date' => $certificate->issued_at->format('F d, Y'),
            'certificate_id' => $certificate->certificate_id,
            'course_duration' => $certificate->course->duration_hours ?? 'N/A',
            'verification_url' => url('/verify-certificate/' . $certificate->certificate_id),
        ];

        // Generate PDF using view template
        $pdf = PDF::loadView('certificates.template', $templateData);
        $pdf->setPaper('A4', 'landscape');
        
        // Save PDF to storage
        $fileName = 'certificates/certificate_' . $certificate->certificate_id . '.pdf';
        $pdfContent = $pdf->output();
        Storage::disk('public')->put($fileName, $pdfContent);

        return $fileName;
    }

    private function generateCertificateId()
    {
        do {
            $certificateId = 'CERT-' . strtoupper(Str::random(4)) . '-' . date('Y') . '-' . strtoupper(Str::random(4));
        } while (Certificate::where('certificate_id', $certificateId)->exists());

        return $certificateId;
    }

    public function verifyCertificate(string $certificateId)
    {
        $certificate = Certificate::where('certificate_id', $certificateId)
            ->with(['user', 'course'])
            ->first();

        if (!$certificate) {
            return [
                'valid' => false,
                'message' => 'Certificate not found'
            ];
        }

        return [
            'valid' => true,
            'certificate' => $certificate,
            'student_name' => $certificate->user->name,
            'course_title' => $certificate->course->title,
            'issued_date' => $certificate->issued_at->format('F d, Y'),
            'certificate_id' => $certificate->certificate_id,
        ];
    }

    public function regenerateCertificate(Certificate $certificate)
    {
        // Delete old PDF if exists
        if ($certificate->certificate_url) {
            Storage::disk('public')->delete($certificate->certificate_url);
        }

        // Generate new PDF
        $pdfPath = $this->generateCertificatePDF($certificate);
        $certificate->update(['certificate_url' => $pdfPath]);

        return $certificate;
    }

    public function bulkGenerateCertificates(Course $course)
    {
        // Get all users who completed the course but don't have certificates
        $completedEnrollments = Enrollment::where('course_id', $course->id)
            ->where('progress_percentage', 100)
            ->whereDoesntHave('user.certificates', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->with('user')
            ->get();

        $certificates = [];

        foreach ($completedEnrollments as $enrollment) {
            try {
                $certificate = $this->generateCertificate($enrollment->user, $course);
                $certificates[] = $certificate;
            } catch (\Exception $e) {
                \Log::error('Failed to generate certificate for user', [
                    'user_id' => $enrollment->user_id,
                    'course_id' => $course->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $certificates;
    }
}
