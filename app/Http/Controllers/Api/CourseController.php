<?php
// app/Http/Controllers/Api/CourseController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseStoreRequest;
use App\Http\Resources\CourseResource;
use App\Http\Resources\CategoryResource;
use App\Models\Course;
use App\Models\Category;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Notifications\NewAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class CourseController extends Controller
{
    public function __construct()
    {
        // Middleware should be applied in routes, not in controller constructor
    }

    public function index(Request $request)
    {
        try {
            $query = Course::with(['instructor', 'category'])
                ->where('status', 'approved') // Only show approved courses publicly
                ->orderBy('created_at', 'desc');

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                      ->orWhere('description', 'like', "%{$searchTerm}%")
                      ->orWhere('short_description', 'like', "%{$searchTerm}%")
                      ->orWhereHas('instructor', function($instructorQuery) use ($searchTerm) {
                          $instructorQuery->where('name', 'like', "%{$searchTerm}%");
                      });
                });
            }

            // Category filter
            if ($request->has('category') && !empty($request->category)) {
            $query->where('category_id', $request->category);
        }

            // Level filter
            if ($request->has('level') && !empty($request->level)) {
            $query->where('level', $request->level);
        }

            // Price filter
            if ($request->has('price') && !empty($request->price)) {
                switch ($request->price) {
                    case 'free':
                $query->where('is_free', true);
                        break;
                    case 'paid':
                $query->where('is_free', false);
                        break;
                    case 'under_50':
                        $query->where('is_free', false)->where('price', '<', 50);
                        break;
                    case 'under_100':
                        $query->where('is_free', false)->where('price', '<', 100);
                        break;
                }
            }

            // Status filter (for authenticated users)
            if (auth()->check() && $request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            // Featured courses filter
            if ($request->has('featured') && $request->boolean('featured')) {
                $query->where('is_featured', true);
            }

            // Instructor filter
            if ($request->has('instructor') && !empty($request->instructor)) {
                $query->where('instructor_id', $request->instructor);
            }

            // Sort options
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            // Validate sort fields
            $allowedSortFields = ['title', 'price', 'created_at', 'updated_at', 'enrollments_count'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = min($request->get('per_page', 12), 50); // Max 50 per page
            $courses = $query->paginate($perPage);

            // Add additional data
            $courses->getCollection()->transform(function ($course) {
                $course->enrollments_count = $course->enrollments()->count();
                $course->lessons_count = $course->lessons()->count();
                $course->average_rating = $course->reviews()->avg('rating') ?? 0;
                $course->reviews_count = $course->reviews()->count();
                
                // Add enrollment status for authenticated users
                if (auth()->check()) {
                    $course->is_enrolled = auth()->user()->isEnrolledIn($course->id);
                } else {
                    $course->is_enrolled = false;
                }
                
                return $course;
            });

        return response()->json([
            'status' => 'success',
            'data' => CourseResource::collection($courses->items()),
            'pagination' => [
                'current_page' => $courses->currentPage(),
                'per_page' => $courses->perPage(),
                'total' => $courses->total(),
                'last_page' => $courses->lastPage(),
                'from' => $courses->firstItem(),
                'to' => $courses->lastItem(),
            ]
        ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching courses', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch courses'
            ], 500);
        }
    }

    public function show($slug)
    {
        $course = Course::with([
            'instructor',
            'category',
            // Load sections and nested lessons ordered for full curriculum view
            'sections' => function ($query) {
                $query->orderBy('sort_order');
            },
            'sections.lessons' => function ($query) {
                $query->orderBy('sort_order');
            },
            'reviews' => function ($query) {
                $query->where('status', 'approved')
                      ->with('user')
                      ->latest();
            }
        ])
        ->withCount(['enrollments', 'reviews'])
        ->withAvg('reviews', 'rating')
        ->where('slug', $slug)
        ->firstOrFail();

        // Also load builder pages. Students see only published pages with active widgets.
        // Instructors/admins (who can edit) see all pages and widgets.
        $canEdit = auth()->check() && (
            auth()->user()->hasRole('admin') || $course->instructor_id === auth()->id()
        );

        if ($canEdit) {
            $course->load([
                'pages' => function ($q) {
                    $q->ordered();
                },
                'pages.widgets' => function ($q) {
                    $q->ordered();
                }
            ]);
        } else {
            $course->load([
                'publishedPages' => function ($q) {
                    $q->ordered();
                },
                'publishedPages.widgets' => function ($q) {
                    $q->active()->ordered();
                }
            ]);
        }

        // Check if user is enrolled
        $isEnrolled = false;
        if (auth()->check()) {
            $isEnrolled = auth()->user()->isEnrolledIn($course->id);
        }



        return response()->json([
            'status' => 'success',
            'data' => [
                'course' => new CourseResource($course),
                'is_enrolled' => $isEnrolled
            ]
        ]);
    }

    /**
     * Test method to debug course creation
     */
    public function testCreate(Request $request)
    {
        \Log::info('Test course creation endpoint called', [
            'user_id' => auth()->id(),
            'user_roles' => auth()->user() ? auth()->user()->getRoleNames() : 'no user',
            'request_data' => $request->all(),
            'request_url' => $request->url(),
            'request_method' => $request->method(),
            'headers' => $request->headers->all()
        ]);

        // Check if user is authenticated
        if (!auth()->check()) {
            \Log::warning('User not authenticated for test endpoint');
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated'
            ], 401);
        }

        // Check if user has instructor role
        if (!auth()->user()->hasRole('instructor')) {
            \Log::warning('User does not have instructor role', [
                'user_roles' => auth()->user()->getRoleNames()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'User does not have instructor role',
                'user_roles' => auth()->user()->getRoleNames()
            ], 403);
        }

        // Check if categories exist
        $categories = Category::count();
        
        \Log::info('Test endpoint successful', [
            'user_id' => auth()->id(),
            'user_roles' => auth()->user()->getRoleNames(),
            'categories_count' => $categories
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Test passed',
            'data' => [
                'user_id' => auth()->id(),
                'user_roles' => auth()->user()->getRoleNames(),
                'categories_count' => $categories,
                'categories' => Category::select('id', 'name')->get()
            ]
        ]);
    }

    public function store(CourseStoreRequest $request)
    {

        try {
        $courseData = $request->all();
            
            // Ensure array fields are properly handled
            if ($request->has('outcomes') && is_array($request->input('outcomes'))) {
                $courseData['outcomes'] = array_filter($request->input('outcomes'), function($item) {
                    return !empty(trim($item));
                });
            }
            
            if ($request->has('requirements') && is_array($request->input('requirements'))) {
                $courseData['requirements'] = array_filter($request->input('requirements'), function($item) {
                    return !empty(trim($item));
                });
            }
            
            // Convert boolean fields to integers for database
            if (isset($courseData['is_featured'])) {
                $courseData['is_featured'] = filter_var($courseData['is_featured'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            }
            
            if (isset($courseData['is_free'])) {
                $courseData['is_free'] = filter_var($courseData['is_free'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            }
            
        $courseData['instructor_id'] = auth()->id();
        $courseData['slug'] = Str::slug($request->title);

            // Debug: Log the processed course data
            \Log::info('Processed course data', $courseData);
            \Log::info('Array fields after processing', [
                'outcomes_final' => $courseData['outcomes'] ?? 'not set',
                'requirements_final' => $courseData['requirements'] ?? 'not set',
                'outcomes_type' => gettype($courseData['outcomes'] ?? null),
                'requirements_type' => gettype($courseData['requirements'] ?? null)
            ]);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('course-thumbnails', 'public');
            $courseData['thumbnail'] = $path;
                \Log::info('Thumbnail uploaded', ['path' => $path]);
            } else {
                // Remove thumbnail field if no valid file is uploaded
                unset($courseData['thumbnail']);
                \Log::info('No valid thumbnail uploaded, removing thumbnail field');
        }

            // Debug: Log final course data before creation
            \Log::info('Final course data before creation', $courseData);

        $course = Course::create($courseData);

            \Log::info('Course created successfully', ['course_id' => $course->id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Course created successfully',
            'data' => new CourseResource($course->load(['instructor', 'category']))
        ], 201);
        } catch (\Exception $e) {
            \Log::error('Course creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $courseData ?? 'no data'
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create course: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Course $course)
    {
        // Check if user can update this course
        if (!auth()->user()->hasRole('admin') && $course->instructor_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'category_id' => 'required|exists:categories,id',
            'level' => 'required|in:beginner,intermediate,advanced',
            'price' => 'required|numeric|min:0',
            'is_free' => 'boolean',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'preview_video' => 'nullable|url',
            'tags' => 'nullable|array',
            'requirements' => 'nullable|array',
            'outcomes' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $courseData = $request->all();

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            
            $path = $request->file('thumbnail')->store('course-thumbnails', 'public');
            $courseData['thumbnail'] = $path;
        }

        $course->update($courseData);

        return response()->json([
            'status' => 'success',
            'message' => 'Course updated successfully',
            'data' => new CourseResource($course->load(['instructor', 'category']))
        ]);
    }

    public function destroy(Course $course)
    {
        // Check if user can delete this course
        if (!auth()->user()->hasRole('admin') && $course->instructor_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        // Delete thumbnail
        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }

        $course->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Course deleted successfully'
        ]);
    }

    public function myCourses(Request $request)
    {
        $query = Course::with(['category', 'reviews'])
            ->withCount(['enrollments', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->where('instructor_id', auth()->id());

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $courses = $query->latest()->paginate($request->get('per_page', 10));

        return response()->json([
            'status' => 'success',
            'data' => CourseResource::collection($courses)
        ]);
    }

    public function updateStatus(Request $request, Course $course)
    {
        // Only admin can update course status
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:draft,pending,approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $course->update([
            'status' => $request->status
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Course status updated successfully',
            'data' => $course
        ]);
    }

    public function getAnnouncements(Course $course)
    {
        // Check if user is enrolled or is instructor/admin
        if (!auth()->user()->hasRole('admin') && 
            $course->instructor_id !== auth()->id() && 
            !auth()->user()->isEnrolledIn($course->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $announcements = $course->announcements()
            ->latest()
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'status' => 'success',
            'data' => $announcements
        ]);
    }

    public function createAnnouncement(Request $request, Course $course)
    {
        // Only instructor or admin can create announcements
        if (!auth()->user()->hasRole('admin') && $course->instructor_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:2000',
            'is_pinned' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $announcement = $course->announcements()->create([
            'title' => $request->title,
            'content' => $request->content,
            'is_pinned' => $request->get('is_pinned', false),
            'created_by' => auth()->id(),
        ]);

        // Send notification to enrolled students
        $enrolledStudents = $course->enrollments()->with('user')->get();
        foreach ($enrolledStudents as $enrollment) {
            $enrollment->user->notify(new NewAnnouncement($announcement));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Announcement created successfully',
            'data' => $announcement
        ], 201);
    }

    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'category_id' => 'nullable|exists:categories,id',
            'level' => 'nullable|in:beginner,intermediate,advanced',
            'price_range' => 'nullable|in:free,paid,under_50,under_100,over_100',
            'duration' => 'nullable|in:short,medium,long',
            'rating' => 'nullable|numeric|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Course::with(['instructor', 'category', 'reviews'])
            ->withCount(['enrollments', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->where('status', 'approved');

        // Search in title, description, and instructor name
        $searchQuery = $request->query;
        $query->where(function ($q) use ($searchQuery) {
            $q->where('title', 'like', "%{$searchQuery}%")
              ->orWhere('description', 'like', "%{$searchQuery}%")
              ->orWhereHas('instructor', function ($q) use ($searchQuery) {
                  $q->where('name', 'like', "%{$searchQuery}%");
              });
        });

        // Apply filters
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->level) {
            $query->where('level', $request->level);
        }

        if ($request->price_range) {
            switch ($request->price_range) {
                case 'free':
                    $query->where('is_free', true);
                    break;
                case 'paid':
                    $query->where('is_free', false);
                    break;
                case 'under_50':
                    $query->where('price', '<=', 50);
                    break;
                case 'under_100':
                    $query->where('price', '<=', 100);
                    break;
                case 'over_100':
                    $query->where('price', '>', 100);
                    break;
            }
        }

        if ($request->duration) {
            switch ($request->duration) {
                case 'short':
                    $query->where('duration', '<=', 60); // 1 hour
                    break;
                case 'medium':
                    $query->where('duration', '>', 60)->where('duration', '<=', 300); // 1-5 hours
                    break;
                case 'long':
                    $query->where('duration', '>', 300); // 5+ hours
                    break;
            }
        }

        if ($request->rating) {
            $query->whereHas('reviews', function ($q) use ($request) {
                $q->havingRaw('AVG(rating) >= ?', [$request->rating]);
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'relevance');
        switch ($sortBy) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'popular':
                $query->orderBy('enrollments_count', 'desc');
                break;
            case 'rating':
                $query->orderBy('reviews_avg_rating', 'desc');
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            default: // relevance
                $query->orderByRaw('MATCH(title, description) AGAINST(? IN BOOLEAN MODE)', [$searchQuery])
                      ->orderBy('enrollments_count', 'desc');
        }

        // Add caching for expensive queries
        $cacheKey = 'course_search_' . md5(serialize($request->all()));
        $courses = Cache::remember($cacheKey, 300, function () use ($query, $request) {
            return $query->paginate($request->get('per_page', 12));
        });

        return response()->json([
            'status' => 'success',
            'data' => CourseResource::collection($courses)
        ]);
    }

    public function getInstructorAnalytics(Request $request)
    {
        // Only instructor or admin can access analytics
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('instructor')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $instructorId = auth()->user()->hasRole('admin') && $request->instructor_id 
            ? $request->instructor_id 
            : auth()->id();

        $courses = Course::where('instructor_id', $instructorId)
            ->withCount(['enrollments', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->get();

        $totalEnrollments = $courses->sum('enrollments_count');
        $totalRevenue = $courses->sum(function ($course) {
            return $course->enrollments_count * $course->price;
        });
        $averageRating = $courses->avg('reviews_avg_rating');

        // Monthly enrollment trend
        $monthlyEnrollments = Enrollment::whereHas('course', function ($q) use ($instructorId) {
            $q->where('instructor_id', $instructorId);
        })
        ->selectRaw('DATE_FORMAT(enrolled_at, "%Y-%m") as month, COUNT(*) as count')
        ->groupBy('month')
        ->orderBy('month', 'desc')
        ->limit(12)
        ->get();

        // Top performing courses
        $topCourses = $courses->sortByDesc('enrollments_count')->take(5);

        return response()->json([
            'status' => 'success',
            'data' => [
                'overview' => [
                    'total_courses' => $courses->count(),
                    'total_enrollments' => $totalEnrollments,
                    'total_revenue' => $totalRevenue,
                    'average_rating' => round($averageRating, 2),
                ],
                'monthly_trend' => $monthlyEnrollments,
                'top_courses' => $topCourses,
                'course_performance' => $courses->map(function ($course) {
                    return [
                        'id' => $course->id,
                        'title' => $course->title,
                        'enrollments' => $course->enrollments_count,
                        'revenue' => $course->enrollments_count * $course->price,
                        'rating' => round($course->reviews_avg_rating, 2),
                    ];
                })
            ]
        ]);
    }

    public function getInstructorStudents(Request $request)
    {
        // Only instructor or admin can access student data
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('instructor')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $instructorId = auth()->user()->hasRole('admin') && $request->instructor_id 
            ? $request->instructor_id 
            : auth()->id();

        $query = User::whereHas('enrollments.course', function ($q) use ($instructorId) {
            $q->where('instructor_id', $instructorId);
        })
        ->with(['enrollments' => function ($q) use ($instructorId) {
            $q->whereHas('course', function ($q) use ($instructorId) {
                $q->where('instructor_id', $instructorId);
            })->with('course:id,title');
        }])
        ->withCount(['enrollments' => function ($q) use ($instructorId) {
            $q->whereHas('course', function ($q) use ($instructorId) {
                $q->where('instructor_id', $instructorId);
            });
        }]);

        // Apply filters
        if ($request->has('course_id')) {
            $query->whereHas('enrollments', function ($q) use ($request) {
                $q->where('course_id', $request->course_id);
            });
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $students
        ]);
    }

    public function getInstructorEarnings(Request $request)
    {
        // Only instructor or admin can access earnings
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('instructor')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $instructorId = auth()->user()->hasRole('admin') && $request->instructor_id 
            ? $request->instructor_id 
            : auth()->id();

        $period = $request->get('period', 'month'); // week, month, year
        $startDate = now()->startOf($period);
        $endDate = now()->endOf($period);

        // Get earnings for the period
        $earnings = Payment::whereHas('course', function ($q) use ($instructorId) {
            $q->where('instructor_id', $instructorId);
        })
        ->where('status', 'completed')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        // Calculate totals
        $totalEarnings = $earnings->sum('total');
        $totalEnrollments = Payment::whereHas('course', function ($q) use ($instructorId) {
            $q->where('instructor_id', $instructorId);
        })
        ->where('status', 'completed')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->count();

        // Platform fee calculation (example: 20% platform fee)
        $platformFee = $totalEarnings * 0.20;
        $netEarnings = $totalEarnings - $platformFee;

        // Course-wise earnings
        $courseEarnings = Payment::whereHas('course', function ($q) use ($instructorId) {
            $q->where('instructor_id', $instructorId);
        })
        ->where('status', 'completed')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->with('course:id,title')
        ->selectRaw('course_id, SUM(amount) as total_earnings, COUNT(*) as enrollments')
        ->groupBy('course_id')
        ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'period' => $period,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'total_earnings' => $totalEarnings,
                'platform_fee' => $platformFee,
                'net_earnings' => $netEarnings,
                'total_enrollments' => $totalEnrollments,
                'daily_earnings' => $earnings,
                'course_earnings' => $courseEarnings,
            ]
        ]);
    }

    /**
     * Get courses for the authenticated instructor
     */
    public function instructorCourses(Request $request)
    {
        $query = Course::with(['category', 'sections.lessons'])
            ->withCount(['enrollments', 'reviews', 'sections'])
            ->withAvg('reviews', 'rating')
            ->where('instructor_id', auth()->id());

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%");
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $courses = $query->orderBy('updated_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $courses
        ]);
    }

    /**
     * Create a course draft (step 1 of course creation)
     */
    public function createDraft(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'category_id' => 'required|exists:categories,id',
            'level' => 'required|in:beginner,intermediate,advanced',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $courseData = $request->only(['title', 'description', 'short_description', 'category_id', 'level']);
        $courseData['instructor_id'] = auth()->id();
        $courseData['slug'] = Str::slug($request->title);
        $courseData['status'] = 'draft';
        $courseData['price'] = 0; // Default price
        $courseData['is_free'] = true; // Default to free

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('course-thumbnails', 'public');
            $courseData['thumbnail'] = $path;
        }

        $course = Course::create($courseData);

        return response()->json([
            'success' => true,
            'message' => 'Course draft created successfully',
            'data' => $course->load(['instructor', 'category'])
        ], 201);
    }

    /**
     * Update course pricing and details (step 3 of course creation)
     */
    public function updatePricing(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        // Check if user owns this course
        if (!$course->isOwnedBy(auth()->id())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'price' => 'required|numeric|min:0',
            'discounted_price' => 'nullable|numeric|min:0|lt:price',
            'is_free' => 'required|boolean',
            'requirements' => 'nullable|array',
            'outcomes' => 'nullable|array',
            'tags' => 'nullable|array',
            'access_type' => 'required|in:lifetime,limited',
            'access_days' => 'required_if:access_type,limited|nullable|integer|min:1',
            'duration_hours' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $course->update($request->only([
            'price', 'discounted_price', 'is_free', 'requirements', 
            'outcomes', 'tags', 'access_type', 'access_days', 'duration_hours'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Course pricing updated successfully',
            'data' => $course->fresh()
        ]);
    }

    /**
     * Publish a course (final step)
     */
    public function publish(Request $request, $id)
    {
        $course = Course::with(['pages.widgets', 'sections.lessons'])->findOrFail($id);

        // Check if user owns this course
        if (!$course->isOwnedBy(auth()->id())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validate course is ready for publishing
        $errors = [];

        if (empty($course->title) || empty($course->description)) {
            $errors[] = 'Course must have title and description';
        }

        // Check for course builder content (pages/widgets) OR traditional content (sections/lessons)
        $hasPages = $course->pages->count() > 0;
        $hasSections = $course->sections->count() > 0;
        
        if (!$hasPages && !$hasSections) {
            $errors[] = 'Course must have at least one page or section';
        }

        // If using pages, check for published pages with active widgets
        if ($hasPages) {
            $publishedPages = $course->pages->where('is_published', true);
            if ($publishedPages->count() === 0) {
                $errors[] = 'Course must have at least one published page with content';
            } else {
                // Check if published pages have active widgets
                $hasActiveContent = false;
                foreach ($publishedPages as $page) {
                    if ($page->widgets->where('is_active', true)->count() > 0) {
                        $hasActiveContent = true;
                        break;
                    }
                }
                if (!$hasActiveContent) {
                    $errors[] = 'Published pages must have active content widgets';
                }
            }
        }

        // If using traditional sections/lessons
        if ($hasSections) {
            $totalLessons = $course->sections->sum(function($section) {
                return $section->lessons->count();
            });

            if ($totalLessons === 0) {
                $errors[] = 'Course must have at least one lesson';
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => 'Course is not ready for publishing',
                'errors' => $errors
            ], 400);
        }

        $course->update(['status' => 'pending']); // Set to pending for admin approval

        return response()->json([
            'success' => true,
            'message' => 'Course submitted for review. It will be published after admin approval.',
            'data' => $course->fresh()
        ]);
    }

    /**
     * Get course with full curriculum (for instructor editing)
     */
    public function getCurriculum($id)
    {
        $course = Course::with([
            'sections' => function($query) {
                $query->orderBy('sort_order');
            },
            'sections.lessons' => function($query) {
                $query->orderBy('sort_order');
            },
            'category'
        ])->findOrFail($id);

        // Check if user owns this course
        if (!$course->isOwnedBy(auth()->id())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $course
        ]);
    }

    /**
     * Delete a course (only if no enrollments)
     */
    public function deleteCourse($id)
    {
        $course = Course::with('enrollments')->findOrFail($id);

        // Check if user owns this course
        if (!$course->isOwnedBy(auth()->id())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if course has enrollments
        if ($course->enrollments->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete course that has enrollments'
            ], 400);
        }

        // Delete thumbnail if exists
        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }

        $course->delete();

        return response()->json([
            'success' => true,
            'message' => 'Course deleted successfully'
        ]);
    }

    public function featured()
    {
        try {
            $featuredCourses = Course::with(['instructor', 'category'])
                ->where('status', 'approved')
                ->where('is_featured', true)
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get()
                ->map(function ($course) {
                    $course->enrollments_count = $course->enrollments()->count();
                    $course->lessons_count = $course->lessons()->count();
                    $course->average_rating = $course->reviews()->avg('rating') ?? 0;
                    $course->reviews_count = $course->reviews()->count();
                    
                    // Add enrollment status for authenticated users
                    if (auth()->check()) {
                        $course->is_enrolled = auth()->user()->isEnrolledIn($course->id);
                    } else {
                        $course->is_enrolled = false;
                    }
                    
                    return $course;
                });

            return response()->json([
                'status' => 'success',
                'data' => CourseResource::collection($featuredCourses)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching featured courses', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch featured courses'
            ], 500);
        }
    }

    public function categories()
    {
        try {
            \Log::info('CourseController: Starting categories method');
            
            // First, try to get categories without the count to see if that's the issue
            $categoriesWithoutCount = Category::orderBy('name')->get();
            \Log::info('CourseController: Categories without count fetched successfully', [
                'count' => $categoriesWithoutCount->count()
            ]);
            
            // Now try with the count
            $categories = Category::withCount('courses')
                ->orderBy('name')
                ->get();
            
            \Log::info('CourseController: Categories with count fetched successfully', [
                'count' => $categories->count(),
                'first_category' => $categories->first() ? $categories->first()->toArray() : null
            ]);

            return response()->json([
                'status' => 'success',
                'data' => CategoryResource::collection($categories)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching categories', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch categories'
            ], 500);
        }
    }
}