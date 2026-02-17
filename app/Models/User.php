<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'bio',
        'avatar',
        'status',
        'social_links',
        'email_verified_at',
        'email_verification_token',
        'otp_code',
        'otp_expires_at',
        'otp_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'social_links' => 'array',
        'otp_expires_at' => 'datetime',
        'otp_verified_at' => 'datetime',
    ];

    // Relationships
    public function instructedCourses()
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class, 'enrollments')
                    ->withPivot(['amount_paid', 'status', 'enrolled_at', 'expires_at', 'progress_percentage'])
                    ->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function studentProducts()
    {
        return $this->hasMany(StudentProduct::class);
    }

    public function forumTopics()
    {
        return $this->hasMany(ForumTopic::class);
    }

    public function forumReplies()
    {
        return $this->hasMany(ForumReply::class);
    }

    public function lessonProgress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function alumni()
    {
        return $this->hasOne(Alumni::class);
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    // Career-related relationships removed as requested

    // Helper methods
    public function isInstructor()
    {
        return $this->hasRole('instructor');
    }

    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    public function isStudent()
    {
        return $this->hasRole('student');
    }

    public function isEnrolledIn($courseId)
    {
        return $this->enrollments()->where('course_id', $courseId)->where('status', 'active')->exists();
    }

    // OTP Methods
    public function generateOTP()
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10), // OTP expires in 10 minutes
            'otp_verified_at' => null,
        ]);
        return $otp;
    }

    public function verifyOTP($otp)
    {
        if (!$this->otp_code || !$this->otp_expires_at) {
            return false;
        }

        if ($this->otp_expires_at->isPast()) {
            return false;
        }

        if ($this->otp_code !== $otp) {
            return false;
        }

        $this->update([
            'otp_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        return true;
    }

    public function isOTPValid()
    {
        return $this->otp_code && 
               $this->otp_expires_at && 
               $this->otp_expires_at->isFuture() &&
               !$this->otp_verified_at;
    }

    public function clearOTP()
    {
        $this->update([
            'otp_code' => null,
            'otp_expires_at' => null,
            'otp_verified_at' => null,
        ]);
    }

    public function generateEmailVerificationToken()
    {
        $token = Str::random(64);
        $this->update([
            'email_verification_token' => $token,
        ]);
        return $token;
    }

    public function verifyEmail()
    {
        $this->update([
            'email_verified_at' => now(),
            'email_verification_token' => null,
        ]);
    }

    public function isEmailVerified()
    {
        return !is_null($this->email_verified_at);
    }
}