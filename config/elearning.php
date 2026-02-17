<?php
// =====================================
// CONFIGURATION FILES
// =====================================

// config/elearning.php
return [
    /*
    |--------------------------------------------------------------------------
    | E-Learning Platform Settings
    |--------------------------------------------------------------------------
    */
    
    'platform' => [
        'name' => env('PLATFORM_NAME', 'E-Learning Platform'),
        'description' => env('PLATFORM_DESCRIPTION', 'Your gateway to unlimited learning'),
        'logo' => env('PLATFORM_LOGO', '/images/logo.png'),
        'favicon' => env('PLATFORM_FAVICON', '/images/favicon.ico'),
    ],

    'features' => [
        'registration_enabled' => env('REGISTRATION_ENABLED', true),
        'email_verification_required' => env('EMAIL_VERIFICATION_REQUIRED', true),
        'course_approval_required' => env('COURSE_APPROVAL_REQUIRED', true),
        'review_moderation_enabled' => env('REVIEW_MODERATION_ENABLED', true),
        'student_product_approval_required' => env('STUDENT_PRODUCT_APPROVAL_REQUIRED', true),
        'certificates_enabled' => env('CERTIFICATES_ENABLED', true),
        'forums_enabled' => env('FORUMS_ENABLED', true),
        'announcements_enabled' => env('ANNOUNCEMENTS_ENABLED', true),
    ],

    'limits' => [
        'max_video_size_mb' => env('MAX_VIDEO_SIZE_MB', 500),
        'max_image_size_mb' => env('MAX_IMAGE_SIZE_MB', 5),
        'max_document_size_mb' => env('MAX_DOCUMENT_SIZE_MB', 10),
        'max_course_lessons' => env('MAX_COURSE_LESSONS', 100),
        'max_quiz_questions' => env('MAX_QUIZ_QUESTIONS', 50),
        'max_quiz_attempts' => env('MAX_QUIZ_ATTEMPTS', 3),
    ],

    'pricing' => [
        'platform_commission_percentage' => env('PLATFORM_COMMISSION_PERCENTAGE', 10),
        'currency' => env('PLATFORM_CURRENCY', 'USD'),
        'currency_symbol' => env('PLATFORM_CURRENCY_SYMBOL', '$'),
    ],
];