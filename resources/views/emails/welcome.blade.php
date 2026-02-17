<?php
// =====================================
// EMAIL TEMPLATES
// =====================================

// resources/views/emails/welcome.blade.php
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to {{ config('app.name') }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4f46e5; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .button { display: inline-block; padding: 12px 24px; background: #4f46e5; color: white; text-decoration: none; border-radius: 5px; }
        .footer { padding: 20px; text-align: center; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Enrollment Confirmed!</h1>
        </div>
        <div class="content">
            <h2>Hello {{ $user->name }}!</h2>
            <p>Congratulations! You have successfully enrolled in:</p>
            
            <div class="course-info">
                <h3>{{ $course->title }}</h3>
                <p><strong>Instructor:</strong> {{ $course->instructor->name }}</p>
                <p><strong>Enrollment Date:</strong> {{ $enrollment->enrolled_at->format('F d, Y') }}</p>
                @if($enrollment->amount_paid > 0)
                <p><strong>Amount Paid:</strong> ${{ number_format($enrollment->amount_paid, 2) }}</p>
                @endif
                @if($enrollment->expires_at)
                <p><strong>Access Until:</strong> {{ $enrollment->expires_at->format('F d, Y') }}</p>
                @endif
            </div>

            <p>You can now access all course materials, participate in discussions, and track your progress.</p>

            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ $courseUrl }}" class="button">Start Learning</a>
            </p>

            <p>Good luck with your learning journey!</p>

            <p>Best regards,<br>
            The {{ config('app.name') }} Team</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>



