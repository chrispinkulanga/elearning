<?php
// =====================================

// resources/views/emails/announcement.blade.php
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $announcement->title }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #3b82f6; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .announcement-content { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .button { display: inline-block; padding: 12px 24px; background: #3b82f6; color: white; text-decoration: none; border-radius: 5px; }
        .footer { padding: 20px; text-align: center; color: #666; font-size: 14px; }
        .unsubscribe { font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📢 Course Announcement</h1>
        </div>
        <div class="content">
            <h2>Hello {{ $user->name }}!</h2>
            <p>There's a new announcement in your course <strong>{{ $course->title }}</strong>:</p>
            
            <div class="announcement-content">
                <h3>{{ $announcement->title }}</h3>
                <div>{!! nl2br(e($announcement->content)) !!}</div>
                <p><small><strong>Posted by:</strong> {{ $announcement->user->name }} on {{ $announcement->created_at->format('F d, Y \a\t g:i A') }}</small></p>
            </div>

            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ $courseUrl }}" class="button">View in Course</a>
            </p>

            <p>Stay engaged with your course community!</p>

            <p>Best regards,<br>
            The {{ config('app.name') }} Team</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p class="unsubscribe">
                <a href="{{ $unsubscribeUrl }}">Unsubscribe from announcements</a>
            </p>
        </div>
    </div>
</body>
</html>