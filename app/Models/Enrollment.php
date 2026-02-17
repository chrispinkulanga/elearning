<?php
// app/Models/Enrollment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'amount_paid',
        'status',
        'enrolled_at',
        'expires_at',
        'progress_percentage',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'expires_at' => 'datetime',
        'amount_paid' => 'decimal:2',
        'progress_percentage' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function updateProgress()
    {
        $totalLessons = $this->course->lessons()->count();
        if ($totalLessons > 0) {
            $completedLessons = LessonProgress::where('user_id', $this->user_id)
                ->whereIn('lesson_id', $this->course->lessons()->pluck('id'))
                ->where('is_completed', true)
                ->count();
            
            $this->progress_percentage = ($completedLessons / $totalLessons) * 100;
            $this->save();
        }
    }
}