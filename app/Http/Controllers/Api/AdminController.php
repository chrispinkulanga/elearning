<?php
// app/Http/Controllers/Api/AdminController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Review;
use App\Models\StudentProduct;
use App\Models\Payment;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Models\Notification; // Added this import for Notification model
use App\Models\Alumni;
use Illuminate\Support\Facades\Mail;
use App\Mail\AlumniInvitationMail;

class AdminController extends Controller
{
    public function getUsers(Request $request)
    {
        $query = User::with('roles');

        if ($request->has('role')) {
            $query->role($request->role);
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:student,instructor,admin',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'bio' => $request->bio,
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        // Assign role
        $user->assignRole($request->role);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => $user->load('roles')
        ], 201);
    }

    public function updateUser(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:student,instructor,admin',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive,banned',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'bio' => $request->bio,
            'status' => $request->status,
        ]);

        // Update role if changed
        if ($request->has('role')) {
            $user->syncRoles([$request->role]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => $user->load('roles')
        ]);
    }

    public function updateUserStatus(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive,banned',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update(['status' => $request->status]);

        return response()->json([
            'status' => 'success',
            'message' => 'User status updated successfully',
            'data' => $user->load('roles')
        ]);
    }

    public function updateUserRole(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:student,instructor,admin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Remove all current roles and assign new one
        $user->syncRoles([$request->role]);

        return response()->json([
            'status' => 'success',
            'message' => 'User role updated successfully',
            'data' => $user->load('roles')
        ]);
    }

    public function deleteUser(User $user)
    {
        // Prevent deleting admin users
        if ($user->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete admin user'
            ], 400);
        }

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete your own account'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully'
        ]);
    }

    public function banUser(User $user)
    {
        $user->update(['status' => 'banned']);

        return response()->json([
            'status' => 'success',
            'message' => 'User banned successfully'
        ]);
    }

    public function unbanUser(User $user)
    {
        $user->update(['status' => 'active']);

        return response()->json([
            'status' => 'success',
            'message' => 'User unbanned successfully'
        ]);
    }

    public function getPendingCourses(Request $request)
    {
        $courses = Course::with(['instructor', 'category'])
            ->where('status', 'pending')
            ->latest()
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $courses
        ]);
    }

    public function getEnrollments(Request $request)
    {
        \Log::info('getEnrollments called with params:', $request->all());
        
        try {
            $query = Enrollment::with(['user', 'course.instructor']);

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('course', function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%");
                    });
                });
            }

            $enrollments = $query->latest()->paginate($request->get('per_page', 15));

            \Log::info('Enrollments found:', [
                'total' => $enrollments->total(),
                'count' => $enrollments->count(),
                'per_page' => $enrollments->perPage(),
                'current_page' => $enrollments->currentPage(),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $enrollments
            ]);
        } catch (\Exception $e) {
            \Log::error('getEnrollments error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get enrollments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getEnrollmentDetails(Enrollment $enrollment)
    {
        $enrollment->load(['user', 'course.instructor', 'lessons']);

        return response()->json([
            'status' => 'success',
            'data' => $enrollment
        ]);
    }

    public function approveEnrollment(Enrollment $enrollment)
    {
        $enrollment->update(['status' => 'approved']);

        return response()->json([
            'status' => 'success',
            'message' => 'Enrollment approved successfully',
            'data' => $enrollment
        ]);
    }

    public function rejectEnrollment(Request $request, Enrollment $enrollment)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $enrollment->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Enrollment rejected successfully',
            'data' => $enrollment
        ]);
    }

    public function deleteEnrollment(Enrollment $enrollment)
    {
        $enrollment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Enrollment deleted successfully'
        ]);
    }

    public function testCourses()
    {
        try {
            $courseCount = Course::count();
            $courses = Course::select('id', 'title', 'status', 'instructor_id')->take(5)->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Courses test',
                'data' => [
                    'total_courses' => $courseCount,
                    'sample_courses' => $courses,
                    'database_connected' => true,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Database connection failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllCourses(Request $request)
    {
        \Log::info('getAllCourses called with params:', $request->all());
        
        try {
        $query = Course::with(['instructor', 'category'])
            ->withCount('enrollments');

            if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('instructor', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $courses = $query->latest()->paginate($request->get('per_page', 15));

            \Log::info('Courses found:', [
                'total' => $courses->total(),
                'count' => $courses->count(),
                'per_page' => $courses->perPage(),
                'current_page' => $courses->currentPage(),
            ]);

        return response()->json([
            'status' => 'success',
            'data' => $courses
        ]);
        } catch (\Exception $e) {
            \Log::error('getAllCourses error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get courses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function approveCourse(Course $course)
    {
        $course->update(['status' => 'approved']);

        // Notify instructor
        $course->instructor->notify(new \App\Notifications\CourseApproved($course));

        return response()->json([
            'status' => 'success',
            'message' => 'Course approved successfully',
            'data' => $course
        ]);
    }

    public function rejectCourse(Request $request, Course $course)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $course->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Course rejected successfully',
            'data' => $course
        ]);
    }

    public function deleteCourse(Course $course)
    {
        // Prevent deletion of courses with active enrollments
        if ($course->enrollments()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete course with active enrollments'
            ], 400);
        }

        $course->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Course deleted successfully'
        ]);
    }

    public function updateCourse(Request $request, Course $course)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'category_id' => 'sometimes|required|exists:categories,id',
            'status' => 'sometimes|required|in:pending,approved,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $course->update($request->only([
            'title', 'description', 'price', 'category_id', 'status'
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Course updated successfully',
            'data' => $course->load('instructor', 'category')
        ]);
    }

    public function getPendingReviews(Request $request)
    {
        $reviews = Review::with(['user', 'course'])
            ->where('status', 'pending')
            ->latest()
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $reviews
        ]);
    }

    public function getAllReviews(Request $request)
    {
        $query = Review::with(['user', 'course']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $reviews = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $reviews
        ]);
    }

    public function getPendingProducts(Request $request)
    {
        $products = StudentProduct::with(['user', 'course'])
            ->where('status', 'pending')
            ->latest()
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    public function getAllProducts(Request $request)
    {
        $query = StudentProduct::with(['user', 'course']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    public function getPayments(Request $request)
    {
        $query = Payment::with(['user', 'course']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('method')) {
            $query->where('payment_method', $request->method);
        }

        $payments = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $payments
        ]);
    }

    public function getPaymentDetails(Payment $payment)
    {
        $payment->load(['user', 'course']);

        return response()->json([
            'status' => 'success',
            'data' => $payment
        ]);
    }

    public function refundPayment(Request $request, Payment $payment)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Process refund logic here (integrate with payment gateway)
        $payment->update([
            'status' => 'refunded',
            'refund_reason' => $request->reason,
            'refunded_at' => now(),
        ]);

        // Cancel enrollment
        $enrollment = Enrollment::where('user_id', $payment->user_id)
            ->where('course_id', $payment->course_id)
            ->first();

        if ($enrollment) {
            $enrollment->update(['status' => 'cancelled']);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Payment refunded successfully'
        ]);
    }

    public function testConnection()
    {
        try {
            $userCount = User::count();
            $courseCount = Course::count();
            $enrollmentCount = Enrollment::count();
            $paymentCount = Payment::count();

            return response()->json([
                'status' => 'success',
                'message' => 'Backend connection test',
                'data' => [
                    'users' => $userCount,
                    'courses' => $courseCount,
                    'enrollments' => $enrollmentCount,
                    'payments' => $paymentCount,
                    'database_connected' => true,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Database connection failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getOverviewReport()
    {
        try {
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

            $userCount = User::count();
            $courseCount = Course::count();
            $enrollmentCount = Enrollment::where('status', 'active')->count();
            $revenueSum = Payment::where('status', 'completed')->sum('amount');

            // Get enrollment trends (last 6 months)
            $enrollmentTrends = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $enrollmentTrends[] = [
                    'date' => $month->format('M Y'),
                    'enrollments' => Enrollment::where('status', 'active')
                        ->whereNotNull('enrolled_at')
                        ->whereYear('enrolled_at', $month->year)
                        ->whereMonth('enrolled_at', $month->month)
                        ->count()
                ];
            }

            // Get course categories distribution
            $categoryDistribution = Course::leftJoin('categories', 'courses.category_id', '=', 'categories.id')
                ->selectRaw('COALESCE(categories.name, "Uncategorized") as name, COUNT(*) as value')
                ->groupBy('categories.id', 'categories.name')
                ->get()
                ->map(function ($item) {
                    return [
                        'name' => $item->name,
                        'value' => (int) $item->value
                    ];
                });

            // If no categories found, create a default entry
            if ($categoryDistribution->isEmpty()) {
                $categoryDistribution = collect([
                    [
                        'name' => 'No Categories',
                        'value' => Course::count()
                    ]
                ]);
            }

            // Get recent users (last 5)
            $recentUsers = User::with('roles')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'status' => $user->status,
                        'created_at' => $user->created_at->format('Y-m-d H:i:s')
                    ];
                });

            // Get recent courses (last 5)
            $recentCourses = Course::with('instructor')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($course) {
                    return [
                        'id' => $course->id,
                        'title' => $course->title,
                        'instructor' => $course->instructor ? [
                            'id' => $course->instructor->id,
                            'name' => $course->instructor->name,
                            'email' => $course->instructor->email
                        ] : null,
                        'status' => $course->status,
                        'enrollments_count' => $course->enrollments()->count(),
                        'created_at' => $course->created_at->format('Y-m-d H:i:s')
                    ];
                });

        $stats = [
                'total_users' => $userCount,
            'new_users_this_month' => User::where('created_at', '>=', $currentMonth)->count(),
            'new_users_last_month' => User::whereBetween('created_at', [$lastMonth, $currentMonth])->count(),
            
                'total_courses' => $courseCount,
            'approved_courses' => Course::where('status', 'approved')->count(),
            'pending_courses' => Course::where('status', 'pending')->count(),
            
                'total_enrollments' => $enrollmentCount,
            'new_enrollments_this_month' => Enrollment::where('enrolled_at', '>=', $currentMonth)->count(),
            
                'total_revenue' => $revenueSum,
            'revenue_this_month' => Payment::where('status', 'completed')
                ->where('created_at', '>=', $currentMonth)->sum('amount'),
            'revenue_last_month' => Payment::where('status', 'completed')
                ->whereBetween('created_at', [$lastMonth, $currentMonth])->sum('amount'),

                // Additional data for charts and lists
                'enrollment_trend' => $enrollmentTrends,
                'category_distribution' => $categoryDistribution,
                'recent_users' => $recentUsers,
                'recent_courses' => $recentCourses,
        ];

        // Calculate growth percentages
        $stats['user_growth'] = $stats['new_users_last_month'] > 0 
            ? (($stats['new_users_this_month'] - $stats['new_users_last_month']) / $stats['new_users_last_month']) * 100 
            : 0;

        $stats['revenue_growth'] = $stats['revenue_last_month'] > 0 
            ? (($stats['revenue_this_month'] - $stats['revenue_last_month']) / $stats['revenue_last_month']) * 100 
            : 0;

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get dashboard stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getSalesReport(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        $sales = Payment::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders, SUM(amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topCourses = Payment::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->join('courses', 'payments.course_id', '=', 'courses.id')
            ->selectRaw('courses.title, COUNT(*) as sales, SUM(payments.amount) as revenue')
            ->groupBy('courses.id', 'courses.title')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'daily_sales' => $sales,
                'top_courses' => $topCourses,
                'period' => $days . ' days'
            ]
        ]);
    }

    public function getEngagementReport(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        // Course completion rates
        $courseStats = Course::withCount([
            'enrollments as total_enrollments',
            'enrollments as completed_enrollments' => function ($query) {
                $query->where('progress_percentage', 100);
            }
        ])->get()->map(function ($course) {
            $course->completion_rate = $course->total_enrollments > 0 
                ? ($course->completed_enrollments / $course->total_enrollments) * 100 
                : 0;
            return $course;
        });

        // Most active users
        $activeUsers = User::whereHas('enrollments', function ($query) use ($startDate) {
            $query->where('enrolled_at', '>=', $startDate);
        })->withCount([
            'enrollments as recent_enrollments' => function ($query) use ($startDate) {
                $query->where('enrolled_at', '>=', $startDate);
            }
        ])->orderByDesc('recent_enrollments')->limit(10)->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'course_completion_rates' => $courseStats->take(10),
                'most_active_users' => $activeUsers,
                'period' => $days . ' days'
            ]
        ]);
    }

    public function getUserReport(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        $userStats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'banned_users' => User::where('status', 'banned')->count(),
            'students' => User::role('student')->count(),
            'instructors' => User::role('instructor')->count(),
            'new_registrations' => User::where('created_at', '>=', $startDate)->count(),
        ];

        // Registration trend
        $registrationTrend = User::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as registrations')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'stats' => $userStats,
                'registration_trend' => $registrationTrend,
                'period' => $days . ' days'
            ]
        ]);
    }

    public function getCourseReport(Request $request)
    {
        $courseStats = [
            'total_courses' => Course::count(),
            'approved_courses' => Course::where('status', 'approved')->count(),
            'pending_courses' => Course::where('status', 'pending')->count(),
            'rejected_courses' => Course::where('status', 'rejected')->count(),
            'free_courses' => Course::where('is_free', true)->count(),
            'paid_courses' => Course::where('is_free', false)->count(),
        ];

        // Courses by category
        $coursesByCategory = Course::join('categories', 'courses.category_id', '=', 'categories.id')
            ->selectRaw('categories.name, COUNT(*) as course_count')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('course_count')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'stats' => $courseStats,
                'courses_by_category' => $coursesByCategory,
            ]
        ]);
    }

    public function getRevenueReport(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        $revenueStats = [
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'period_revenue' => Payment::where('status', 'completed')
                ->where('created_at', '>=', $startDate)->sum('amount'),
            'total_transactions' => Payment::where('status', 'completed')->count(),
            'average_transaction' => Payment::where('status', 'completed')->avg('amount'),
        ];

        // Revenue by payment method
        $revenueByMethod = Payment::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('payment_method, COUNT(*) as transactions, SUM(amount) as revenue')
            ->groupBy('payment_method')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'stats' => $revenueStats,
                'revenue_by_method' => $revenueByMethod,
                'period' => $days . ' days'
            ]
        ]);
    }

    public function getSettings()
    {
        // Return system settings
        $settings = [
            'site_name' => config('app.name'),
            'site_description' => 'E-Learning Platform',
            'registration_enabled' => true,
            'course_approval_required' => true,
            'review_moderation_enabled' => true,
            'student_product_approval_required' => true,
            'commission_rate' => 10, // Platform commission percentage
            'refund_policy_days' => 30,
        ];

        return response()->json([
            'status' => 'success',
            'data' => $settings
        ]);
    }

    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'registration_enabled' => 'boolean',
            'course_approval_required' => 'boolean',
            'review_moderation_enabled' => 'boolean',
            'student_product_approval_required' => 'boolean',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'refund_policy_days' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update settings in database or config files
        // This is a simplified implementation
        
        return response()->json([
            'status' => 'success',
            'message' => 'Settings updated successfully'
        ]);
    }

    public function bulkDeleteUsers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Prevent deleting admin users and current user
        $userIds = array_filter($request->user_ids, function ($id) {
            $user = User::find($id);
            return $user && !$user->hasRole('admin') && $id !== auth()->id();
        });

        User::whereIn('id', $userIds)->delete();

        return response()->json([
            'status' => 'success',
            'message' => count($userIds) . ' users deleted successfully'
        ]);
    }

    public function bulkApproveCourses(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_ids' => 'required|array',
            'course_ids.*' => 'exists:courses,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        Course::whereIn('id', $request->course_ids)->update(['status' => 'approved']);

        return response()->json([
            'status' => 'success',
            'message' => count($request->course_ids) . ' courses approved successfully'
        ]);
    }

    public function bulkModerateReviews(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'review_ids' => 'required|array',
            'review_ids.*' => 'exists:reviews,id',
            'status' => 'required|in:approved,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        Review::whereIn('id', $request->review_ids)->update(['status' => $request->status]);

        return response()->json([
            'status' => 'success',
            'message' => count($request->review_ids) . ' reviews ' . $request->status . ' successfully'
        ]);
    }

    public function createBackup()
    {
        try {
            $backupName = 'backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
            $backupPath = storage_path('backups/' . $backupName);
            
            // Create backups directory if it doesn't exist
            if (!file_exists(storage_path('backups'))) {
                mkdir(storage_path('backups'), 0755, true);
            }

            // Create database backup using mysqldump
            $command = sprintf(
                'mysqldump -h%s -P%s -u%s -p%s %s > %s',
                config('database.connections.mysql.host'),
                config('database.connections.mysql.port'),
                config('database.connections.mysql.username'),
                config('database.connections.mysql.password'),
                config('database.connections.mysql.database'),
                $backupPath
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Backup creation failed'
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Backup created successfully',
                'data' => [
                    'backup_name' => $backupName,
                    'backup_path' => $backupPath,
                    'created_at' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Backup creation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSystemLogs(Request $request)
    {
        $logs = collect([
            [
                'id' => 1,
                'level' => 'info',
                'message' => 'System started successfully',
                'timestamp' => now()->subHours(2)->toISOString(),
            ],
            [
                'id' => 2,
                'level' => 'warning',
                'message' => 'High memory usage detected',
                'timestamp' => now()->subHours(4)->toISOString(),
            ],
            [
                'id' => 3,
                'level' => 'error',
                'message' => 'Database connection failed',
                'timestamp' => now()->subHours(6)->toISOString(),
            ],
        ]);

            return response()->json([
            'status' => 'success',
            'data' => $logs
        ]);
    }

    public function getNotifications()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->orWhereNull('user_id') // System notifications for all users
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'time' => $notification->created_at->diffForHumans(),
                    'read' => $notification->read,
                    'type' => $notification->type,
                    'data' => $notification->data
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $notifications
        ]);
    }

    public function markNotificationAsRead(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|exists:notifications,id'
        ]);

        $notification = Notification::find($request->notification_id);
        
        // Check if user can read this notification
        if ($notification->user_id && $notification->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $notification->update(['read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read'
        ]);
    }

    public function markAllNotificationsAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->orWhereNull('user_id')
            ->update(['read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read'
        ]);
    }

    public function createNotification(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:course,user,payment,system',
            'user_id' => 'nullable|exists:users,id',
            'related_id' => 'nullable|integer',
            'related_type' => 'nullable|string',
            'data' => 'nullable|array'
        ]);

        $notification = Notification::create([
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'user_id' => $request->user_id,
            'related_id' => $request->related_id,
            'related_type' => $request->related_type,
            'data' => $request->data
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notification created successfully',
            'data' => $notification
        ]);
    }

    // Alumni Management Methods
    public function getAlumni(Request $request)
    {
        $query = Alumni::with('user.roles');

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $alumni = $query->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $alumni
        ]);
    }

    public function createAlumni(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'graduation_year' => 'nullable|string|max:4',
            'degree' => 'nullable|string|max:255',
            'major' => 'nullable|string|max:255',
            'achievements' => 'nullable|string',
            'current_position' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'linkedin_url' => 'nullable|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user already has alumni record
        $existingAlumni = Alumni::where('user_id', $request->user_id)->first();
        if ($existingAlumni) {
            return response()->json([
                'status' => 'error',
                'message' => 'User already has an alumni record'
            ], 409);
        }

        $alumni = Alumni::create([
            'user_id' => $request->user_id,
            'graduation_year' => $request->graduation_year,
            'degree' => $request->degree,
            'major' => $request->major,
            'achievements' => $request->achievements,
            'current_position' => $request->current_position,
            'company' => $request->company,
            'linkedin_url' => $request->linkedin_url,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Alumni record created successfully',
            'data' => $alumni->load('user.roles')
        ], 201);
    }

    public function updateAlumni(Request $request, Alumni $alumni)
    {
        $validator = Validator::make($request->all(), [
            'graduation_year' => 'nullable|string|max:4',
            'degree' => 'nullable|string|max:255',
            'major' => 'nullable|string|max:255',
            'achievements' => 'nullable|string',
            'current_position' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'status' => 'nullable|in:active,inactive,pending',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $alumni->update($request->only([
            'graduation_year',
            'degree',
            'major',
            'achievements',
            'current_position',
            'company',
            'linkedin_url',
            'status'
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Alumni record updated successfully',
            'data' => $alumni->load('user.roles')
        ]);
    }

    public function deleteAlumni(Alumni $alumni)
    {
        $alumni->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Alumni record deleted successfully'
        ]);
    }

    public function inviteToAlumni(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'message' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::findOrFail($request->user_id);

        // Check if user already has alumni record
        $existingAlumni = Alumni::where('user_id', $user->id)->first();
        if ($existingAlumni) {
            return response()->json([
                'status' => 'error',
                'message' => 'User already has an alumni record'
            ], 409);
        }

        // Create pending alumni record
        $alumni = Alumni::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'invited_at' => now(),
        ]);

        // Create notification for the user
        $notification = Notification::create([
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $user->id,
            'type' => 'alumni_invitation',
            'data' => [
                'title' => 'Alumni Network Invitation',
                'message' => $request->message ?: 'You have been invited to join our alumni network! Click here to accept the invitation.',
                'alumni_id' => $alumni->id
            ],
            'read_at' => null,
        ]);

        // Send email notification
        try {
            Mail::to($user->email)->send(new AlumniInvitationMail($alumni, $request->message));
            \Log::info('Alumni invitation email sent successfully to: ' . $user->email);
        } catch (\Exception $e) {
            \Log::error('Failed to send alumni invitation email: ' . $e->getMessage());
            // Don't fail the request if email fails - the invitation is still created
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Alumni invitation sent successfully',
            'data' => $alumni->load('user.roles')
        ]);
    }

    public function acceptAlumniInvitation(Request $request, Alumni $alumni)
    {
        if ($alumni->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'This invitation is no longer valid'
            ], 400);
        }

        $alumni->update([
            'status' => 'active',
            'joined_at' => now(),
        ]);

        // Mark notification as read
        Notification::where('notifiable_id', $alumni->user_id)
            ->where('notifiable_type', 'App\\Models\\User')
            ->where('type', 'alumni_invitation')
            ->whereRaw("JSON_EXTRACT(data, '$.alumni_id') = ?", [$alumni->id])
            ->update(['read_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Alumni invitation accepted successfully',
            'data' => $alumni->load('user.roles')
        ]);
    }

    public function getEligibleUsers(Request $request)
    {
        // Get users who are students or instructors but not already alumni
        $query = User::with('roles')
            ->whereDoesntHave('alumni')
            ->where(function ($q) {
                $q->whereHas('roles', function ($roleQuery) {
                    $roleQuery->whereIn('name', ['student', 'instructor']);
                });
            });

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }
}