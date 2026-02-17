<?php
// app/Http/Controllers/Api/EnrollmentController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EnrollmentController extends Controller
{
    public function enroll(Request $request, Course $course)
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            \Log::error('Enrollment attempt by unauthenticated user');
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication required'
            ], 401);
        }

        // Check if course exists
        if (!$course) {
            \Log::error('Course not found for enrollment', ['course_id' => $request->route('course')]);
            return response()->json([
                'status' => 'error',
                'message' => 'Course not found'
            ], 404);
        }

        \Log::info('Enrollment attempt', [
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email,
            'course_id' => $course->id,
            'course_title' => $course->title,
            'course_is_free' => $course->is_free,
            'course_price' => $course->price,
            'request_data' => $request->all()
        ]);

        // Check if already enrolled
        if (auth()->user()->isEnrolledIn($course->id)) {
            \Log::info('User already enrolled', ['user_id' => auth()->id(), 'course_id' => $course->id]);
            return response()->json([
                'status' => 'error',
                'message' => 'Already enrolled in this course'
            ], 400);
        }

        $enrollmentData = [
            'user_id' => auth()->id(),
            'course_id' => $course->id,
            'enrolled_at' => now(),
            'status' => 'active',
        ];

        // Handle free course
        if ($course->is_free || $course->price == 0) {
            \Log::info('Processing free course enrollment', [
                'user_id' => auth()->id(),
                'course_id' => $course->id,
                'enrollment_data' => $enrollmentData
            ]);
            
            $enrollmentData['amount_paid'] = 0;
            
            try {
                $enrollment = Enrollment::create($enrollmentData);

                \Log::info('Free course enrollment successful', [
                    'enrollment_id' => $enrollment->id,
                    'user_id' => auth()->id(),
                    'course_id' => $course->id
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Successfully enrolled in the course',
                    'data' => $enrollment->load('course')
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to create enrollment', [
                    'error' => $e->getMessage(),
                    'user_id' => auth()->id(),
                    'course_id' => $course->id,
                    'enrollment_data' => $enrollmentData
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to create enrollment: ' . $e->getMessage()
                ], 500);
            }
        }

        // For paid courses, ensure the course is published/approved
        if ($course->status !== 'approved') {
            return response()->json([
                'status' => 'error',
                'message' => 'Course is not available for paid enrollment'
            ], 400);
        }

        // Handle paid course
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:stripe,paypal,flutterwave',
            'payment_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Process payment
        $paymentResult = $this->processPayment($request, $course);

        if ($paymentResult['status'] === 'success') {
            $enrollmentData['amount_paid'] = $course->getCurrentPriceAttribute();
            
            // Set expiry date if limited access
            if ($course->access_type === 'limited' && $course->access_days) {
                $enrollmentData['expires_at'] = now()->addDays($course->access_days);
            }

            $enrollment = Enrollment::create($enrollmentData);

            // Create payment record
            Payment::create([
                'transaction_id' => Str::uuid(),
                'user_id' => auth()->id(),
                'course_id' => $course->id,
                'amount' => $course->getCurrentPriceAttribute(),
                'currency' => 'USD',
                'payment_method' => $request->payment_method,
                'status' => 'completed',
                'gateway_transaction_id' => $paymentResult['transaction_id'],
                'gateway_response' => $paymentResult['response'],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully enrolled in the course',
                'data' => $enrollment->load('course')
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Payment failed: ' . $paymentResult['message']
        ], 400);
    }

    public function myEnrollments(Request $request)
    {
        $query = auth()->user()->enrollments()
            ->with(['course.instructor', 'course.category'])
            ->latest();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $enrollments = $query->paginate($request->get('per_page', 10));

        // Transform the data to include proper course information with thumbnails
        $transformedEnrollments = $enrollments->getCollection()->map(function ($enrollment) {
            return [
                'id' => $enrollment->id,
                'user_id' => $enrollment->user_id,
                'course_id' => $enrollment->course_id,
                'enrolled_at' => $enrollment->enrolled_at,
                'status' => $enrollment->status,
                'amount_paid' => $enrollment->amount_paid,
                'expires_at' => $enrollment->expires_at,
                'created_at' => $enrollment->created_at,
                'updated_at' => $enrollment->updated_at,
                'progress' => $enrollment->progress ?? 0, // Add progress field
                'course' => $enrollment->course ? [
                    'id' => $enrollment->course->id,
                    'title' => $enrollment->course->title,
                    'slug' => $enrollment->course->slug,
                    'description' => $enrollment->course->description,
                    'short_description' => $enrollment->course->short_description,
                    'level' => $enrollment->course->level,
                    'price' => $enrollment->course->price,
                    'is_free' => $enrollment->course->is_free,
                    'status' => $enrollment->course->status,
                    'thumbnail' => $enrollment->course->thumbnail ? asset('storage/' . $enrollment->course->thumbnail) : null,
                    'image' => $enrollment->course->thumbnail ? asset('storage/' . $enrollment->course->thumbnail) : null,
                    'preview_video' => $enrollment->course->preview_video,
                    'is_featured' => $enrollment->course->is_featured,
                    'total_lessons' => $enrollment->course->total_lessons ?? 0,
                    'total_duration' => $enrollment->course->total_duration ?? 0,
                    'average_rating' => $enrollment->course->average_rating ?? 0,
                    'instructor' => $enrollment->course->instructor ? [
                        'id' => $enrollment->course->instructor->id,
                        'name' => $enrollment->course->instructor->name,
                        'email' => $enrollment->course->instructor->email,
                    ] : null,
                    'category' => $enrollment->course->category ? [
                        'id' => $enrollment->course->category->id,
                        'name' => $enrollment->course->category->name,
                    ] : null,
                ] : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'data' => $transformedEnrollments,
                'current_page' => $enrollments->currentPage(),
                'last_page' => $enrollments->lastPage(),
                'per_page' => $enrollments->perPage(),
                'total' => $enrollments->total(),
            ]
        ]);
    }

    public function unenroll(Course $course)
    {
        $enrollment = auth()->user()->enrollments()
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not enrolled in this course'
            ], 404);
        }

        $enrollment->update(['status' => 'cancelled']);

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully unenrolled from the course'
        ]);
    }

    private function processPayment($request, $course)
    {
        // This is a simplified payment processing example
        // In production, you would integrate with actual payment gateways
        
        if ($request->payment_method === 'stripe') {
            return $this->processStripePayment($request, $course);
        } elseif ($request->payment_method === 'paypal') {
            return $this->processPayPalPayment($request, $course);
        } elseif ($request->payment_method === 'flutterwave') {
            return $this->processFlutterwavePayment($request, $course);
        }

        return [
            'status' => 'error',
            'message' => 'Unsupported payment method'
        ];
    }

    private function processStripePayment($request, $course)
    {
        // Stripe payment processing would go here
        // For demo purposes, returning success
        return [
            'status' => 'success',
            'transaction_id' => 'stripe_' . Str::random(20),
            'response' => ['demo' => 'success']
        ];
    }

    private function processPayPalPayment($request, $course)
    {
        // PayPal payment processing would go here
        // For demo purposes, returning success
        return [
            'status' => 'success',
            'transaction_id' => 'paypal_' . Str::random(20),
            'response' => ['demo' => 'success']
        ];
    }

    private function processFlutterwavePayment($request, $course)
    {
        // Flutterwave processing placeholder
        // In production: verify transaction reference with Flutterwave API
        return [
            'status' => 'success',
            'transaction_id' => 'flutterwave_' . Str::random(20),
            'response' => ['demo' => 'success']
        ];
    }
}