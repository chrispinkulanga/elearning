<?php
// app/Http/Controllers/Api/CertificateController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PDF; // You'll need to install dompdf: composer require barryvdh/laravel-dompdf

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $certificates = auth()->user()->certificates()
            ->with('course:id,title,instructor_id')
            ->latest()
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'status' => 'success',
            'data' => $certificates
        ]);
    }

    public function show(Certificate $certificate)
    {
        // Check if certificate belongs to current user
        if ($certificate->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $certificate->load(['course.instructor', 'user']);

        return response()->json([
            'status' => 'success',
            'data' => $certificate
        ]);
    }

    public function generate(Course $course)
    {
        // Check if user is enrolled and completed the course
        $enrollment = auth()->user()->enrollments()
            ->where('course_id', $course->id)
            ->where('progress_percentage', 100)
            ->first();

        if (!$enrollment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Course not completed or not enrolled'
            ], 400);
        }

        // Check if certificate already exists
        $existingCertificate = auth()->user()->certificates()
            ->where('course_id', $course->id)
            ->first();

        if ($existingCertificate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Certificate already generated for this course',
                'data' => $existingCertificate
            ], 400);
        }

        // Generate certificate
        $certificateId = 'CERT-' . strtoupper(Str::random(8)) . '-' . date('Y');
        
        $certificate = Certificate::create([
            'certificate_id' => $certificateId,
            'user_id' => auth()->id(),
            'course_id' => $course->id,
            'issued_at' => now(),
        ]);

        // Generate PDF certificate
        $pdfPath = $this->generateCertificatePDF($certificate);
        $certificate->update(['certificate_url' => $pdfPath]);

        return response()->json([
            'status' => 'success',
            'message' => 'Certificate generated successfully',
            'data' => $certificate->load(['course', 'user'])
        ], 201);
    }

    public function download(Certificate $certificate)
    {
        // Check if certificate belongs to current user
        if ($certificate->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        if (!$certificate->certificate_url || !Storage::disk('public')->exists($certificate->certificate_url)) {
            // Regenerate certificate if file doesn't exist
            $pdfPath = $this->generateCertificatePDF($certificate);
            $certificate->update(['certificate_url' => $pdfPath]);
        }

        $filePath = storage_path('app/public/' . $certificate->certificate_url);
        $fileName = 'Certificate_' . $certificate->certificate_id . '.pdf';

        return response()->download($filePath, $fileName);
    }

    public function verify($certificateId)
    {
        $certificate = Certificate::where('certificate_id', $certificateId)
            ->with(['user:id,name', 'course:id,title'])
            ->first();

        if (!$certificate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Certificate not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Certificate is valid',
            'data' => [
                'certificate_id' => $certificate->certificate_id,
                'student_name' => $certificate->user->name,
                'course_title' => $certificate->course->title,
                'issued_at' => $certificate->issued_at->format('F d, Y'),
                'is_valid' => true
            ]
        ]);
    }

    private function generateCertificatePDF(Certificate $certificate)
    {
        $certificate->load(['user', 'course.instructor']);
        
        $data = [
            'certificate' => $certificate,
            'student_name' => $certificate->user->name,
            'course_title' => $certificate->course->title,
            'instructor_name' => $certificate->course->instructor->name,
            'completion_date' => $certificate->issued_at->format('F d, Y'),
            'certificate_id' => $certificate->certificate_id,
        ];

        // Generate PDF using a view template
        $pdf = PDF::loadView('certificates.template', $data);
        
        // Save PDF to storage
        $fileName = 'certificates/certificate_' . $certificate->certificate_id . '.pdf';
        $pdfContent = $pdf->output();
        Storage::disk('public')->put($fileName, $pdfContent);

        return $fileName;
    }

    public function getCertificatesByInstructor(Request $request)
    {
        // Only for instructors - get certificates for their courses
        if (!auth()->user()->hasRole('instructor')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $certificates = Certificate::whereHas('course', function ($query) {
            $query->where('instructor_id', auth()->id());
        })
        ->with(['user:id,name', 'course:id,title'])
        ->latest()
        ->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $certificates
        ]);
    }
}