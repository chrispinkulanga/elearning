<?php
// app/Models/Lesson.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'video_url',
        'video_duration',
        'content',
        'attachments',
        'is_preview',
        'sort_order',
        'course_id',
        'section_id',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_preview' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function progress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function userProgress()
    {
        return $this->hasOne(LessonProgress::class)->where('user_id', auth()->id());
    }
}