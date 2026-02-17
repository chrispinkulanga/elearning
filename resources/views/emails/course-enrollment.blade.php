<?php
// =====================================

// resources/views/emails/course-enrollment.blade.php
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Course Enrollment Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #10b981; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .course-info { background: white; padding: 15px; border-radius: 8px; margin: 20px 0; }
        .button { display: inline-block; padding: 12px 24px; background: #10b981; color: white; text-decoration: none; border-radius: 5px; }
        .footer { padding: 20px),
        'min_course_price' => env('MIN_COURSE_PRICE', 5),
        'max_course_price' => env('MAX_COURSE_PRICE', 1000),
    ],

    'certificates' => [
        'template' => env('CERTIFICATE_TEMPLATE', 'certificates.template'),
        'format' => env('CERTIFICATE_FORMAT', 'pdf'), // pdf, image
        'orientation' => env('CERTIFICATE_ORIENTATION', 'landscape'), // landscape, portrait
        'auto_generate' => env('CERTIFICATE_AUTO_GENERATE', true),
        'email_delivery' => env('CERTIFICATE_EMAIL_DELIVERY', true),
    ],

    'notifications' => [
        'email_notifications' => env('EMAIL_NOTIFICATIONS_ENABLED', true),
        'push_notifications' => env('PUSH_NOTIFICATIONS_ENABLED', false),
        'sms_notifications' => env('SMS_NOTIFICATIONS_ENABLED', false),
        'slack_notifications' => env('SLACK_NOTIFICATIONS_ENABLED', false),
    ],

    'storage' => [
        'videos_disk' => env('VIDEOS_STORAGE_DISK', 'public'),
        'images_disk' => env('IMAGES_STORAGE_DISK', 'public'),
        'documents_disk' => env('DOCUMENTS_STORAGE_DISK', 'public'),
        'certificates_disk' => env('CERTIFICATES_STORAGE_DISK', 'public'),
    ],

    'integrations' => [
        'zoom_enabled' => env('ZOOM_INTEGRATION_ENABLED', false),
        'google_meet_enabled' => env('GOOGLE_MEET_ENABLED', false),
        'youtube_enabled' => env('YOUTUBE_INTEGRATION_ENABLED', true),
        'vimeo_enabled' => env('VIMEO_INTEGRATION_ENABLED', true),
    ],

    'security' => [
        'video_protection' => env('VIDEO_PROTECTION_ENABLED', true),
        'download_protection' => env('DOWNLOAD_PROTECTION_ENABLED', true),
        'watermark_enabled' => env('WATERMARK_ENABLED', false),
        'ip_restriction_enabled' => env('IP_RESTRICTION_ENABLED', false),
    ],
];
