<?php
// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ForumController;
use App\Http\Controllers\Api\ForumCategoryController;
use App\Http\Controllers\Api\StudentProductController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\CourseBuilderController;
use App\Http\Controllers\Api\ClickPesaController;
use Illuminate\Support\Facades\Validator;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Direct public routes (for backwards compatibility)
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Public routes - No authentication required
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('verify-email', [AuthController::class, 'verifyEmail']);
    
    // OTP-based password reset routes
    Route::post('send-otp', [AuthController::class, 'sendOTP']);
    Route::post('verify-otp', [AuthController::class, 'verifyOTP']);
    Route::post('reset-password-otp', [AuthController::class, 'resetPasswordWithOTP']);
});

// Public course routes (no authentication required)
Route::get('courses', [CourseController::class, 'index'])->middleware('throttle:60,1');
Route::get('courses/{slug}', [CourseController::class, 'show']);

// Public categories route (no authentication required)
Route::get('categories', [CategoryController::class, 'index']);

// Public student products (Talent Place)
Route::get('talent-place', [StudentProductController::class, 'index']);
Route::get('talent-place/{product}', [StudentProductController::class, 'show']);

// Health check endpoint
Route::get('health', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Backend is running',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

// ClickPesa routes (public - no authentication required)
Route::prefix('clickpesa')->group(function () {
    Route::post('generate-token', [ClickPesaController::class, 'generateToken']);
    Route::post('preview-ussd-push', [ClickPesaController::class, 'previewUssdPush']);
    Route::post('initiate-ussd-push', [ClickPesaController::class, 'initiateUssdPush']);
    Route::get('payment-status/{transactionId}', [ClickPesaController::class, 'getPaymentStatus']);
});

// Test categories endpoint
Route::get('test-categories', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Categories routing is working',
        'timestamp' => now()
    ]);
});

// Test array processing endpoint
Route::post('test-array', function (Request $request) {
    \Log::info('Test array endpoint called', [
        'all_data' => $request->all(),
        'outcomes' => $request->input('outcomes'),
        'outcomes_type' => gettype($request->input('outcomes')),
        'requirements' => $request->input('requirements'),
        'requirements_type' => gettype($request->input('requirements'))
    ]);
    
    return response()->json([
        'status' => 'success',
        'message' => 'Array test completed',
        'received_data' => [
            'outcomes' => $request->input('outcomes'),
            'outcomes_type' => gettype($request->input('outcomes')),
            'requirements' => $request->input('requirements'),
            'requirements_type' => gettype($request->input('requirements')),
            'all_inputs' => $request->all()
        ]
    ]);
});

// Test course validation endpoint
Route::post('test-course-validation', function (Request $request) {
    $rules = [
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'category_id' => 'required|exists:categories,id',
        'level' => 'required|in:beginner,intermediate,advanced',
        'price' => 'required|numeric|min:0',
    ];
    
    // Only add thumbnail validation if a file is actually uploaded
    if ($request->hasFile('thumbnail')) {
        $rules['thumbnail'] = 'image|mimes:jpeg,png,jpg|max:2048';
    }
    
    $validator = Validator::make($request->all(), $rules);
    
    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }
    
    return response()->json([
        'status' => 'success',
        'message' => 'Validation passed',
        'rules_applied' => $rules,
        'data_received' => $request->all()
    ]);
});

// Protected routes - Authentication required
Route::middleware('auth:sanctum')->group(function () {
    
    // Authentication routes for logged-in users
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
    });

    // Dashboard routes based on user role
    Route::prefix('dashboard')->group(function () {
        Route::get('student', [DashboardController::class, 'studentDashboard']);
        Route::get('instructor', [DashboardController::class, 'instructorDashboard']);
        Route::get('admin', [DashboardController::class, 'adminDashboard']);
    });

    // Instructor specific analytics routes
    Route::middleware('role:instructor')->prefix('instructor')->group(function () {
        Route::get('students', [DashboardController::class, 'getInstructorStudents']);
        Route::get('analytics', [DashboardController::class, 'getInstructorAnalytics']);
    });

    // User profile management routes (basic profile info)
    Route::prefix('profile')->group(function () {
        Route::get('/', [UserController::class, 'profile']);
        Route::put('/', [UserController::class, 'updateProfile']);
        Route::post('avatar', [UserController::class, 'uploadAvatar']);
        Route::put('password', [UserController::class, 'changePassword']);
    });

    // Course management routes
    Route::prefix('courses')->group(function () {
        // Test endpoint first
        Route::get('test-routing', function () {
            return response()->json([
                'status' => 'success',
                'message' => 'Courses routing is working correctly',
                'timestamp' => now()
            ]);
        });
        
        // Test my-courses endpoint
        Route::get('test-my-courses', function () {
            return response()->json([
                'status' => 'success',
                'message' => 'My courses endpoint is accessible',
                'user_id' => auth()->id(),
                'timestamp' => now()
            ]);
        });
        
        // Specific routes first (before dynamic routes)
        Route::get('test-create', [CourseController::class, 'testCreate']);
        Route::get('my-courses', [CourseController::class, 'myCourses']);
        Route::get('featured', [CourseController::class, 'featured'])->middleware('throttle:60,1');
        Route::get('categories', [CourseController::class, 'categories']);
        
        // Course CRUD operations
        Route::post('/', [CourseController::class, 'store'])->middleware('throttle:10,1');
        Route::put('{course}', [CourseController::class, 'update'])->middleware('throttle:10,1');
        Route::delete('{course}', [CourseController::class, 'destroy']);
        Route::put('{course}/status', [CourseController::class, 'updateStatus']);
        Route::get('{course}/announcements', [CourseController::class, 'getAnnouncements']);
        Route::post('{course}/announcements', [CourseController::class, 'createAnnouncement'])->middleware('throttle:10,1');
        
        // Course enrollment
        Route::post('{course}/enroll', [EnrollmentController::class, 'enroll']);
        
        // Course comments
        Route::prefix('{course}/comments')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\CourseCommentController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Api\CourseCommentController::class, 'store']);
            Route::get('/stats', [\App\Http\Controllers\Api\CourseCommentController::class, 'stats']);
            Route::put('{comment}', [\App\Http\Controllers\Api\CourseCommentController::class, 'update']);
            Route::delete('{comment}', [\App\Http\Controllers\Api\CourseCommentController::class, 'destroy']);
        });
        
        // Course reviews
        Route::prefix('{course}/reviews')->group(function () {
            Route::get('/', [ReviewController::class, 'index']);
            Route::post('/', [ReviewController::class, 'store']);
            Route::put('{review}', [ReviewController::class, 'update']);
            Route::delete('{review}', [ReviewController::class, 'destroy']);
        });
        
        // Forum routes
        Route::prefix('{course}/forums')->group(function () {
            Route::get('/', [ForumController::class, 'index']);
            Route::get('{forum}/topics', [ForumController::class, 'getTopics']);
            Route::post('{forum}/topics', [ForumController::class, 'createTopic']);
            Route::get('{forum}/topics/{topic}', [ForumController::class, 'getTopic']);
            Route::post('{forum}/topics/{topic}/replies', [ForumController::class, 'replyToTopic']);
            Route::put('{forum}/topics/{topic}/pin', [ForumController::class, 'pinTopic']);
            Route::put('{forum}/topics/{topic}/lock', [ForumController::class, 'lockTopic']);
            Route::get('search', [ForumController::class, 'searchTopics']);
        });
        
        // Dynamic route must be last
        Route::get('{course}', [CourseController::class, 'show']);
    });

    // Category management routes (consolidated)
    Route::prefix('categories')->group(function () {
        Route::post('/', [CategoryController::class, 'store']);
        Route::get('{category}', [CategoryController::class, 'show']);
        Route::put('{category}', [CategoryController::class, 'update']);
        Route::delete('{category}', [CategoryController::class, 'destroy']);
        
        // Admin-only routes
        Route::middleware('role:admin')->group(function () {
            Route::put('{category}/toggle-status', [CategoryController::class, 'toggleStatus']);
        });
    });

    // Enrollment management
    Route::get('my-enrollments', [EnrollmentController::class, 'myEnrollments']);
    
    // Progress tracking
    Route::prefix('progress')->group(function () {
        Route::post('mark-complete', [ProgressController::class, 'markComplete']);
        Route::get('course/{course}', [ProgressController::class, 'getCourseProgress']);
    });

    // Profile management (basic profile only)
    Route::prefix('profile')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\ProfileController::class, 'getProfile']);
        Route::put('/', [App\Http\Controllers\Api\ProfileController::class, 'updateProfile']);
    });

    // Public profile viewing removed (career features not needed)

    // Student products management
    Route::prefix('student-products')->group(function () {
        Route::post('/', [StudentProductController::class, 'store']);
        Route::get('my-products', [StudentProductController::class, 'myProducts']);
        Route::put('{product}', [StudentProductController::class, 'update']);
        Route::delete('{product}', [StudentProductController::class, 'destroy']);
    });

    // Payment routes for users
    Route::prefix('payments')->group(function () {
        Route::get('history', [PaymentController::class, 'getPaymentHistory']);
        Route::get('{payment}', [PaymentController::class, 'getPaymentDetails']);
        Route::get('{payment}/invoice', [PaymentController::class, 'downloadInvoice']);
        Route::post('{payment}/refund', [PaymentController::class, 'requestRefund']);
        // Flutterwave
        Route::post('flutterwave/verify', [PaymentController::class, 'verifyFlutterwave']);
    });





    // Test routes to check if API is reachable
    Route::get('test-forum', function() {
        \Log::info('=== TEST FORUM ROUTE REACHED ===');
        return response()->json([
            'status' => 'success',
            'message' => 'Forum test route is working',
            'timestamp' => now()
        ]);
    });
    
    Route::post('test-forum-post', function(Request $request) {
        \Log::info('=== TEST FORUM POST ROUTE REACHED ===');
        \Log::info('Request method: ' . $request->method());
        \Log::info('Request body: ' . json_encode($request->all()));
        return response()->json([
            'status' => 'success',
            'message' => 'Forum test POST route is working',
            'received_data' => $request->all(),
            'timestamp' => now()
        ]);
    });

    // Standalone forum routes - Public viewing (no authentication required)
    Route::prefix('forums')->group(function () {
        Route::get('/', [ForumController::class, 'getAllForums']);
        Route::get('topics', [ForumController::class, 'getAllTopics']);
        Route::get('topics/{topic}', [ForumController::class, 'getStandaloneTopic']);
        Route::get('search', [ForumController::class, 'searchAllTopics']);
    });

    // Standalone forum routes - Protected actions (require authentication)
    Route::middleware('auth:sanctum')->prefix('forums')->group(function () {
        Route::post('topics', [ForumController::class, 'createStandaloneTopic']);
        Route::put('topics/{topic}', [ForumController::class, 'updateStandaloneTopic']);
        Route::delete('topics/{topic}', [ForumController::class, 'deleteStandaloneTopic']);
        Route::post('topics/{topic}/replies', [ForumController::class, 'createStandaloneReply']);
        
        // Like/unlike routes
        Route::post('topics/{topic}/like', [ForumController::class, 'likeStandaloneTopic']);
        Route::post('replies/{reply}/like', [ForumController::class, 'likeStandaloneReply']);
        
        // Moderation routes (admin/instructor only)
        Route::put('topics/{topic}/pin', [ForumController::class, 'pinStandaloneTopic']);
        Route::put('topics/{topic}/lock', [ForumController::class, 'lockStandaloneTopic']);
        
        // Poll routes
        Route::post('topics/{topic}/polls', [ForumController::class, 'createPoll']);
        Route::post('topics/{topic}/polls/{poll}/vote', [ForumController::class, 'votePoll']);
        Route::get('topics/{topic}/polls/{poll}/results', [ForumController::class, 'getPollResults']);
        
        // Attachment routes
        Route::post('topics/{topic}/attachments', [ForumController::class, 'uploadAttachment']);
        Route::delete('topics/{topic}/attachments/{attachment}', [ForumController::class, 'deleteAttachment']);
        Route::get('attachments/info', [ForumController::class, 'getAttachmentInfo']);
    });

    // Forum category management routes
    Route::prefix('forum-categories')->group(function () {
        Route::get('/', [ForumCategoryController::class, 'index']);
        Route::get('{forumCategory}', [ForumCategoryController::class, 'show']);
        
        // Admin-only routes
        Route::middleware('role:admin')->group(function () {
            Route::post('/', [ForumCategoryController::class, 'store']);
            Route::put('{forumCategory}', [ForumCategoryController::class, 'update']);
            Route::delete('{forumCategory}', [ForumCategoryController::class, 'destroy']);
        });
    });

    // Course-specific forum routes (for course-specific discussions)
    Route::prefix('courses/{course}/forums')->group(function () {
        Route::get('/', [ForumController::class, 'index']);
        Route::get('{forum}/topics', [ForumController::class, 'getTopics']);
        Route::post('{forum}/topics', [ForumController::class, 'createTopic']);
        Route::get('{forum}/topics/{topic}', [ForumController::class, 'getTopic']);
        Route::post('{forum}/topics/{topic}/replies', [ForumController::class, 'replyToTopic']);
        Route::put('{forum}/topics/{topic}/pin', [ForumController::class, 'pinTopic']);
        Route::put('{forum}/topics/{topic}/lock', [ForumController::class, 'lockTopic']);
        Route::get('search', [ForumController::class, 'searchTopics']);
        
        // Poll routes
        Route::post('{forum}/topics/{topic}/polls', [ForumController::class, 'createPoll']);
        Route::post('{forum}/topics/{topic}/polls/{poll}/vote', [ForumController::class, 'votePoll']);
        Route::get('{forum}/topics/{topic}/polls/{poll}/results', [ForumController::class, 'getPollResults']);
        
        // Attachment routes
        Route::post('{forum}/topics/{topic}/attachments', [ForumController::class, 'uploadAttachment']);
        Route::delete('{forum}/topics/{topic}/attachments/{attachment}', [ForumController::class, 'deleteAttachment']);
    });

    // Forum interaction routes
    Route::post('forum-replies/{reply}/upvote', [ForumController::class, 'upvoteReply']);

    // Certificate routes
    Route::get('my-certificates', [UserController::class, 'getCertificates']);
    Route::get('certificates/{certificate}/download', [UserController::class, 'downloadCertificate']);

    // Notification routes
    Route::get('notifications', [UserController::class, 'getNotifications']);
    Route::get('notifications/unread-count', [UserController::class, 'getUnreadCount']);
    Route::put('notifications/{notificationId}/read', [UserController::class, 'markNotificationRead']);
    Route::put('notifications/mark-all-read', [UserController::class, 'markAllNotificationsRead']);
    Route::delete('notifications/{notificationId}', [UserController::class, 'deleteNotification']);

    // Search routes
    Route::get('search/courses', [CourseController::class, 'search']);
    Route::get('search/instructors', [UserController::class, 'searchInstructors']);

    // Alumni invitation acceptance (for all authenticated users)
    Route::post('alumni/{alumni}/accept', [AdminController::class, 'acceptAlumniInvitation']);

    // Instructor specific routes
    Route::middleware('role:instructor|admin')->prefix('instructor')->group(function () {
        Route::get('analytics', [CourseController::class, 'getInstructorAnalytics']);
        Route::get('students', [CourseController::class, 'getInstructorStudents']);
        Route::get('earnings', [CourseController::class, 'getInstructorEarnings']);
        
        // Course Creation & Management
        Route::prefix('courses')->group(function () {
            Route::get('/', [CourseController::class, 'instructorCourses']);
            Route::post('draft', [CourseController::class, 'createDraft']);
            Route::get('{id}/curriculum', [CourseController::class, 'getCurriculum']);
            Route::put('{id}/pricing', [CourseController::class, 'updatePricing']);
            Route::post('{id}/publish', [CourseController::class, 'publish']);
            Route::delete('{id}', [CourseController::class, 'deleteCourse']);
        });

        // Section Management
        Route::prefix('sections')->group(function () {
            Route::get('course/{courseId}', [SectionController::class, 'index']);
            Route::post('/', [SectionController::class, 'store']);
            Route::get('{id}', [SectionController::class, 'show']);
            Route::put('{id}', [SectionController::class, 'update']);
            Route::delete('{id}', [SectionController::class, 'destroy']);
            Route::post('course/{courseId}/reorder', [SectionController::class, 'reorder']);
        });

        // Lesson Management
        Route::prefix('lessons')->group(function () {
            Route::post('section/{sectionId}', [LessonController::class, 'storeInSection']);
            Route::put('{id}', [LessonController::class, 'updateLesson']);
            Route::delete('{id}', [LessonController::class, 'deleteLesson']);
            Route::post('section/{sectionId}/reorder', [LessonController::class, 'reorderLessons']);
            Route::post('upload-video', [LessonController::class, 'uploadVideo']);
        });

        // Note: Category management moved to main categories group above
    });

    // Course builder routes (for instructors)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('courses')->group(function () {
            // Course builder interface
            Route::get('{course}/builder', [CourseBuilderController::class, 'show']);
            
            // Page management
            Route::post('{course}/pages', [CourseBuilderController::class, 'addPage']);
            Route::put('{course}/pages/{page}', [CourseBuilderController::class, 'updatePage']);
            Route::delete('{course}/pages/{page}', [CourseBuilderController::class, 'deletePage']);
            Route::post('{course}/pages/{page}/duplicate', [CourseBuilderController::class, 'duplicatePage']);
            
            // Widget management
            Route::post('pages/{page}/widgets', [CourseBuilderController::class, 'addWidget']);
            Route::put('widgets/{widget}', [CourseBuilderController::class, 'updateWidget']);
            Route::delete('widgets/{widget}', [CourseBuilderController::class, 'deleteWidget']);
            
            // Content reordering
            Route::post('{course}/reorder', [CourseBuilderController::class, 'reorderContent']);
            
            // Template management
            Route::get('templates', [CourseBuilderController::class, 'getTemplates']);
            Route::post('{course}/apply-template', [CourseBuilderController::class, 'applyTemplate']);
            Route::post('{course}/save-as-template', [CourseBuilderController::class, 'saveAsTemplate']);
        });
    });

    // Admin only routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        
        // Test connection
        Route::get('test', [AdminController::class, 'testConnection']);
        Route::get('test-courses', [AdminController::class, 'testCourses']);
        
        // User management
        Route::prefix('users')->group(function () {
            Route::get('/', [AdminController::class, 'getUsers']);
            Route::post('/', [AdminController::class, 'createUser']);
            Route::put('{user}', [AdminController::class, 'updateUser']);
            Route::put('{user}/status', [AdminController::class, 'updateUserStatus']);
            Route::put('{user}/role', [AdminController::class, 'updateUserRole']);
            Route::delete('{user}', [AdminController::class, 'deleteUser']);
            Route::post('{user}/ban', [AdminController::class, 'banUser']);
            Route::post('{user}/unban', [AdminController::class, 'unbanUser']);
        });

        // Course management
        Route::prefix('courses')->group(function () {
            Route::get('pending', [AdminController::class, 'getPendingCourses']);
            Route::get('all', [AdminController::class, 'getAllCourses']);
            Route::put('{course}', [AdminController::class, 'updateCourse']);
            Route::put('{course}/approve', [AdminController::class, 'approveCourse']);
            Route::put('{course}/reject', [AdminController::class, 'rejectCourse']);
            Route::delete('{course}', [AdminController::class, 'deleteCourse']);
        });

        // Category management
        Route::prefix('categories')->group(function () {
            Route::get('/', [CategoryController::class, 'index']);
            Route::post('/', [CategoryController::class, 'store']);
            Route::put('{category}', [CategoryController::class, 'update']);
            Route::delete('{category}', [CategoryController::class, 'destroy']);
        });

        // Review moderation
        Route::prefix('reviews')->group(function () {
            Route::get('pending', [AdminController::class, 'getPendingReviews']);
            Route::put('{review}/moderate', [ReviewController::class, 'moderate']);
            Route::get('all', [AdminController::class, 'getAllReviews']);
        });

        // Student product moderation
        Route::prefix('student-products')->group(function () {
            Route::get('pending', [AdminController::class, 'getPendingProducts']);
            Route::put('{product}/moderate', [StudentProductController::class, 'moderate']);
            Route::post('import-csv', [StudentProductController::class, 'importFromCsv']);
            Route::get('all', [AdminController::class, 'getAllProducts']);
        });

        // Enrollment management
        Route::prefix('enrollments')->group(function () {
            Route::get('/', [AdminController::class, 'getEnrollments']);
            Route::get('{enrollment}', [AdminController::class, 'getEnrollmentDetails']);
            Route::patch('{enrollment}/approve', [AdminController::class, 'approveEnrollment']);
            Route::patch('{enrollment}/reject', [AdminController::class, 'rejectEnrollment']);
            Route::delete('{enrollment}', [AdminController::class, 'deleteEnrollment']);
        });

        // Payment and financial management
        Route::prefix('payments')->group(function () {
            Route::get('/', [AdminController::class, 'getPayments']);
            Route::get('{payment}', [AdminController::class, 'getPaymentDetails']);
            Route::post('{payment}/refund', [AdminController::class, 'refundPayment']);
        });

        // Reporting routes
        Route::prefix('reports')->group(function () {
            Route::get('overview', [AdminController::class, 'getOverviewReport']);
            Route::get('sales', [AdminController::class, 'getSalesReport']);
            Route::get('engagement', [AdminController::class, 'getEngagementReport']);
            Route::get('users', [AdminController::class, 'getUserReport']);
            Route::get('courses', [AdminController::class, 'getCourseReport']);
            Route::get('revenue', [AdminController::class, 'getRevenueReport']);
        });

        // System settings
        Route::prefix('settings')->group(function () {
            Route::get('/', [AdminController::class, 'getSettings']);
            Route::put('/', [AdminController::class, 'updateSettings']);
            Route::post('backup', [AdminController::class, 'createBackup']);
            Route::get('logs', [AdminController::class, 'getSystemLogs']);
        });

        // Notifications
        Route::get('notifications', [AdminController::class, 'getNotifications']);
        Route::post('notifications/mark-read', [AdminController::class, 'markNotificationAsRead']);
        Route::post('notifications/mark-all-read', [AdminController::class, 'markAllNotificationsAsRead']);
        Route::post('notifications', [AdminController::class, 'createNotification']);

        // Alumni management
        Route::prefix('alumni')->group(function () {
            Route::get('/', [AdminController::class, 'getAlumni']);
            Route::post('/', [AdminController::class, 'createAlumni']);
            Route::put('{alumni}', [AdminController::class, 'updateAlumni']);
            Route::delete('{alumni}', [AdminController::class, 'deleteAlumni']);
            Route::post('invite', [AdminController::class, 'inviteToAlumni']);
            Route::get('eligible-users', [AdminController::class, 'getEligibleUsers']);
        });

        // Bulk operations
        Route::prefix('bulk')->group(function () {
            Route::post('users/delete', [AdminController::class, 'bulkDeleteUsers']);
            Route::post('courses/approve', [AdminController::class, 'bulkApproveCourses']);
            Route::post('reviews/moderate', [AdminController::class, 'bulkModerateReviews']);
        });
    });
});

// Fallback route for API
Route::fallback(function(){
    return response()->json([
        'status' => 'error',
        'message' => 'API endpoint not found'
    ], 404);
});