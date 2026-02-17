<?php
// =====================================

// resources/views/emails/certificate.blade.php
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificate of Completion</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f59e0b; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .certificate-info { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #f59e0b; }
        .button { display: inline-block; padding: 12px 24px; background: #f59e0b; color: white; text-decoration: none; border-radius: 5px; margin: 10px; }
        .footer { padding: 20px; text-align: center; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏆 Congratulations!</h1>
        </div>
        <div class="content">
            <h2>Hello {{ $user->name }}!</h2>
            <p>We're thrilled to inform you that you have successfully completed the course and earned your certificate!</p>
            
            <div class="certificate-info">
                <h3>Certificate Details</h3>
                <p><strong>Course:</strong> {{ $course->title }}</p>
                <p><strong>Instructor:</strong> {{ $course->instructor->name }}</p>
                <p><strong>Completion Date:</strong> {{ $certificate->issued_at->format('F d, Y') }}</p>
                <p><strong>Certificate ID:</strong> {{ $certificate->certificate_id }}</p>
            </div>

            <p>Your certificate is attached to this email and is also available in your dashboard.</p>

            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ $certificateUrl }}" class="button">View Certificate</a>
                <a href="{{ $verificationUrl }}" class="button">Verify Certificate</a>
            </p>

            <p>Share your achievement on social media and showcase your new skills!</p>

            <p>Congratulations once again!<br>
            The {{ config('app.name') }} Team</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
