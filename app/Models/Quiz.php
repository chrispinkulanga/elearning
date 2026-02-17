<?php
// app/Models/Quiz.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'time_limit',
        'max_attempts',
        'passing_score',
        'show_results_immediately',
        'course_id',
    ];

    protected $casts = [
        'passing_score' => 'decimal:2',
        'show_results_immediately' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('sort_order');
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function userAttempts()
    {
        return $this->hasMany(QuizAttempt::class)->where('user_id', auth()->id());
    }
}