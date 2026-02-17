<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            width: 60px;
            height: 60px;
            background-color: #3b82f6;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
        }
        h1 {
            color: #1f2937;
            margin: 0;
            font-size: 28px;
        }
        .content {
            text-align: center;
            margin-bottom: 30px;
        }
        .message {
            font-size: 16px;
            color: #6b7280;
            margin-bottom: 30px;
            line-height: 1.8;
        }
        .verify-button {
            display: inline-block;
            background-color: #fbbf24;
            color: #1f2937;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            transition: background-color 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .verify-button:hover {
            background-color: #f59e0b;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #9ca3af;
            font-size: 14px;
        }
        .warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            color: #92400e;
        }
        .link-fallback {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9fafb;
            border-radius: 6px;
            font-size: 14px;
            color: #6b7280;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">E</div>
            <h1>Check your email</h1>
        </div>
        
        <div class="content">
            <div class="message">
                <p>We have sent a verification mail to <strong style="color: #3b82f6;">{{ $user->email }}</strong>.</p>
                <p>To complete your registration and start using our platform, please click on the verification link below:</p>
            </div>
            
            <a href="{{ $verificationUrl }}" class="verify-button">
                Verify Your Email
            </a>
            
            <div class="warning">
                <strong>Do you not see the email?</strong> Check your spam folder.
            </div>
            
            <div class="link-fallback">
                <p>If the button doesn't work, copy and paste this link into your browser:</p>
                <p>{{ $verificationUrl }}</p>
            </div>
        </div>
        
        <div class="footer">
            <p>This verification link will expire in 24 hours.</p>
            <p>If you didn't create an account, please ignore this email.</p>
            <p>&copy; {{ date('Y') }} E-Learning Platform. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
