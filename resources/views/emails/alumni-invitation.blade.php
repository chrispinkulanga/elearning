<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alumni Network Invitation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4f46e5;
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .button {
            display: inline-block;
            background-color: #4f46e5;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #4338ca;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .highlight {
            background-color: #dbeafe;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎓 Alumni Network Invitation</h1>
        <p>Join our growing community of successful graduates</p>
    </div>
    
    <div class="content">
        <h2>Hello {{ $alumni->user->name }}!</h2>
        
        <p>We're excited to invite you to join our exclusive alumni network! As someone who has been part of our learning community, we believe you would be a valuable addition to our alumni network.</p>
        
        @if($message)
        <div class="highlight">
            <strong>Personal Message:</strong><br>
            {{ $message }}
        </div>
        @endif
        
        <h3>What you'll get as an alumni member:</h3>
        <ul>
            <li>🔗 Connect with fellow alumni and expand your professional network</li>
            <li>📚 Access to exclusive alumni resources and continued learning opportunities</li>
            <li>🎯 Share your professional achievements and updates</li>
            <li>👥 Mentor current students and help shape the next generation</li>
            <li>🎉 Invitations to alumni events, webinars, and networking opportunities</li>
        </ul>
        
        <div style="text-align: center;">
            <a href="{{ $invitationUrl }}" class="button">Accept Invitation</a>
        </div>
        
        <p><strong>Note:</strong> This invitation is personal and secure. Please do not share this link with others.</p>
        
        <p>If you have any questions about the alumni network or this invitation, please don't hesitate to reach out to us.</p>
        
        <p>Best regards,<br>
        The Alumni Relations Team</p>
    </div>
    
    <div class="footer">
        <p>This email was sent to {{ $alumni->user->email }}. If you no longer wish to receive these emails, please contact us.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>
