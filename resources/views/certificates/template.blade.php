<?php
// =====================================

// resources/views/certificates/template.blade.php
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        body { 
            font-family: 'Georgia', serif; 
            margin: 0; 
            padding: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
        }
        .certificate {
            background: white;
            padding: 60px;
            text-align: center;
            border: 20px solid #f8f9fa;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            height: 100vh;
            box-sizing: border-box;
        }
        .header { margin-bottom: 40px; }
        .title { 
            font-size: 48px; 
            font-weight: bold; 
            color: #2d3748; 
            margin: 20px 0;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        .subtitle { 
            font-size: 24px; 
            color: #4a5568; 
            margin-bottom: 40px;
        }
        .student-name { 
            font-size: 36px; 
            font-weight: bold; 
            color: #2b6cb0; 
            margin: 30px 0;
            text-decoration: underline;
        }
        .course-title { 
            font-size: 28px; 
            font-style: italic; 
            color: #2d3748; 
            margin: 30px 0;
        }
        .details { 
            margin: 40px 0; 
            font-size: 18px; 
            color: #4a5568;
        }
        .signature { 
            margin-top: 60px; 
            display: flex; 
            justify-content: space-between;
            align-items: flex-end;
        }
        .signature-line { 
            border-top: 2px solid #333; 
            width: 200px; 
            text-align: center; 
            padding-top: 10px;
        }
        .certificate-id { 
            position: absolute; 
            bottom: 20px; 
            right: 20px; 
            font-size: 12px; 
            color: #999;
        }
        .logo { 
            font-size: 32px; 
            font-weight: bold; 
            color: #2b6cb0;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="header">
            <div class="logo">{{ config('app.name') }}</div>
        </div>
        
        <div class="title">Certificate of Completion</div>
        <div class="subtitle">This is to certify that</div>
        
        <div class="student-name">{{ $student_name }}</div>
        
        <div class="subtitle">has successfully completed the course</div>
        
        <div class="course-title">"{{ $course_title }}"</div>
        
        <div class="details">
            <p>Instructor: {{ $instructor_name }}</p>
            <p>Date of Completion: {{ $completion_date }}</p>
            @if(isset($course_duration))
            <p>Course Duration: {{ $course_duration }} hours</p>
            @endif
        </div>
        
        <div class="signature">
            <div class="signature-line">
                <div>{{ $instructor_name }}</div>
                <div style="font-size: 14px;">Course Instructor</div>
            </div>
            <div class="signature-line">
                <div>{{ config('app.name') }}</div>
                <div style="font-size: 14px;">Learning Platform</div>
            </div>
        </div>
        
        <div class="certificate-id">
            Certificate ID: {{ $certificate_id }}<br>
            Verify at: {{ $verification_url ?? url('/verify-certificate/' . $certificate_id) }}
        </div>
    </div>
</body>
</html>