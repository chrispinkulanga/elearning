<?php
// app/Http/Controllers/Api/DashboardController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function studentDashboard()
    {
        $user = auth()->user();

        $enrollments = $user->enrollments()
            ->with('course:id,title,thumbnail')
            ->where('status', 'active')
            ->latest()
            ->limit(5)
            ->get();

        $stats = [
            'total_courses' => $user->enrollments()->where('status', 'active')->count(),
            'completed_courses' => $user->enrollments()->where('progress_percentage', 100)->count(),
            'in_progress_courses' => $user->enrollments()
                ->where('status', 'active')
                ->where('progress_percentage', '>', 0)
                ->where('progress_percentage', '<', 100)
                ->count(),
            'certificates_earned' => $user->certificates()->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user,
                'recent_enrollments' => $enrollments,
                'stats' => $stats,
            ]
        ]);
    }

    public function instructorDashboard()
    {
        try {
            $user = auth()->user();
            
            // Get instructor's courses
            $instructorCourses = $user->instructedCourses();
            $courseIds = $instructorCourses->pluck('id');
            
            // Basic stats
            $totalCourses = $instructorCourses->count();
            $publishedCourses = $instructorCourses->where('status', 'approved')->count();
            $pendingCourses = $instructorCourses->where('status', 'pending')->count();
            
            // Student analytics
            $totalStudents = Enrollment::whereIn('course_id', $courseIds)
                ->where('status', 'active')
                ->distinct('user_id')
                ->count();
                
            $newStudentsThisMonth = Enrollment::whereIn('course_id', $courseIds)
                ->where('status', 'active')
                ->where('created_at', '>=', now()->startOfMonth())
                ->distinct('user_id')
                ->count();
            
            // Revenue analytics
            $totalRevenue = Payment::whereIn('course_id', $courseIds)
                ->where('status', 'completed')
                ->sum('amount');
                
            $monthlyRevenue = Payment::whereIn('course_id', $courseIds)
                ->where('status', 'completed')
                ->where('created_at', '>=', now()->startOfMonth())
                ->sum('amount');
            
            // Course completion analytics
            $totalEnrollments = Enrollment::whereIn('course_id', $courseIds)
                ->where('status', 'active')
                ->count();
                
            $completedEnrollments = Enrollment::whereIn('course_id', $courseIds)
                ->where('status', 'active')
                ->where('progress_percentage', 100)
                ->count();
                
            $completionRate = $totalEnrollments > 0 ? round(($completedEnrollments / $totalEnrollments) * 100, 1) : 0;
            
            // Average rating
            $averageRating = $instructorCourses->avg('average_rating') ?? 0;
            
            // Recent courses with detailed info
            $recentCourses = $instructorCourses()
                ->withCount('enrollments')
                ->with(['category:id,name'])
                ->latest()
                ->limit(5)
                ->get();
            
            // Recent enrollments
            $recentEnrollments = Enrollment::whereIn('course_id', $courseIds)
                ->with(['student:id,name,email', 'course:id,title'])
                ->where('status', 'active')
                ->latest()
                ->limit(10)
                ->get();
            
            // Course performance data
            $coursePerformance = $instructorCourses()
                ->withCount('enrollments')
                ->with(['category:id,name'])
                ->get()
                ->map(function ($course) {
                    $enrollments = $course->enrollments()->where('status', 'active');
                    $completed = $enrollments->where('progress_percentage', 100)->count();
                    $total = $enrollments->count();
                    
                    return [
                        'id' => $course->id,
                        'title' => $course->title,
                        'category' => $course->category->name ?? 'Uncategorized',
                        'total_enrollments' => $course->enrollments_count,
                        'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
                        'revenue' => Payment::where('course_id', $course->id)
                            ->where('status', 'completed')
                            ->sum('amount'),
                        'rating' => $course->average_rating ?? 0,
                        'status' => $course->status,
                        'created_at' => $course->created_at
                    ];
                });

            $stats = [
                'total_courses' => $totalCourses,
                'published_courses' => $publishedCourses,
                'pending_courses' => $pendingCourses,
                'total_students' => $totalStudents,
                'new_students_this_month' => $newStudentsThisMonth,
                'total_revenue' => round($totalRevenue, 2),
                'monthly_revenue' => round($monthlyRevenue, 2),
                'total_enrollments' => $totalEnrollments,
                'completion_rate' => $completionRate,
                'average_rating' => round($averageRating, 1),
            ];

            return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => $user,
                    'recent_courses' => $recentCourses,
                    'recent_enrollments' => $recentEnrollments,
                    'course_performance' => $coursePerformance,
                    'stats' => $stats,
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Instructor Dashboard Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load dashboard data: ' . $e->getMessage(),
                'data' => [
                    'user' => null,
                    'recent_courses' => [],
                    'recent_enrollments' => [],
                    'course_performance' => [],
                    'stats' => [
                        'total_courses' => 0,
                        'published_courses' => 0,
                        'pending_courses' => 0,
                        'total_students' => 0,
                        'new_students_this_month' => 0,
                        'total_revenue' => 0,
                        'monthly_revenue' => 0,
                        'total_enrollments' => 0,
                        'completion_rate' => 0,
                        'average_rating' => 0,
                    ],
                ]
            ], 500);
        }
    }

    public function getInstructorStudents(Request $request)
    {
        try {
            $user = auth()->user();
            $courseIds = $user->instructedCourses()->pluck('id');
            
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search');
            $courseId = $request->get('course_id');
            $status = $request->get('status', 'active');
            
            $query = Enrollment::whereIn('course_id', $courseIds)
                ->with(['student:id,name,email,created_at', 'course:id,title,thumbnail'])
                ->where('status', $status);
            
            if ($search) {
                $query->whereHas('student', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }
            
            if ($courseId) {
                $query->where('course_id', $courseId);
            }
            
            $enrollments = $query->latest()
                ->paginate($perPage);
            
            // Get course options for filter
            $courses = $user->instructedCourses()
                ->select('id', 'title')
                ->get();
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'enrollments' => $enrollments,
                    'courses' => $courses,
                    'filters' => [
                        'search' => $search,
                        'course_id' => $courseId,
                        'status' => $status,
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Get Instructor Students Error: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load students data: ' . $e->getMessage(),
                'data' => [
                    'enrollments' => [],
                    'courses' => [],
                    'filters' => []
                ]
            ], 500);
        }
    }

    public function getInstructorAnalytics(Request $request)
    {
        try {
            $user = auth()->user();
            $courseIds = $user->instructedCourses()->pluck('id');
            $period = $request->get('period', '30'); // days
            
            $startDate = now()->subDays($period);
            
            // Enrollment trends
            $enrollmentTrends = Enrollment::whereIn('course_id', $courseIds)
                ->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            // Revenue trends
            $revenueTrends = Payment::whereIn('course_id', $courseIds)
                ->where('status', 'completed')
                ->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            // Course performance
            $coursePerformance = $user->instructedCourses()
                ->withCount('enrollments')
                ->with(['category:id,name'])
                ->get()
                ->map(function ($course) {
                    $enrollments = $course->enrollments()->where('status', 'active');
                    $completed = $enrollments->where('progress_percentage', 100)->count();
                    $total = $enrollments->count();
                    
                    return [
                        'id' => $course->id,
                        'title' => $course->title,
                        'category' => $course->category->name ?? 'Uncategorized',
                        'enrollments' => $course->enrollments_count,
                        'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
                        'revenue' => Payment::where('course_id', $course->id)
                            ->where('status', 'completed')
                            ->sum('amount'),
                        'rating' => $course->average_rating ?? 0,
                    ];
                });
            
            // Top performing courses
            $topCourses = $coursePerformance
                ->sortByDesc('enrollments')
                ->take(5)
                ->values();
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'enrollment_trends' => $enrollmentTrends,
                    'revenue_trends' => $revenueTrends,
                    'course_performance' => $coursePerformance,
                    'top_courses' => $topCourses,
                    'period' => $period
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Get Instructor Analytics Error: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load analytics data: ' . $e->getMessage(),
                'data' => [
                    'enrollment_trends' => [],
                    'revenue_trends' => [],
                    'course_performance' => [],
                    'top_courses' => [],
                    'period' => $period ?? '30'
                ]
            ], 500);
        }
    }

    public function adminDashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_students' => User::role('student')->count(),
            'total_instructors' => User::role('instructor')->count(),
            'total_courses' => Course::count(),
            'pending_courses' => Course::where('status', 'pending')->count(),
            'total_enrollments' => Enrollment::where('status', 'active')->count(),
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
        ];

        // Recent activities
        $recentCourses = Course::with('instructor:id,name')
            ->latest()
            ->limit(5)
            ->get();

        $recentUsers = User::latest()
            ->limit(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'stats' => $stats,
                'recent_courses' => $recentCourses,
                'recent_users' => $recentUsers,
            ]
        ]);
    }
}