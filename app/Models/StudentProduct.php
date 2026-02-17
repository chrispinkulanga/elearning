<?php
// app/Models/StudentProduct.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'images',
        'video_url',
        'files',
        'user_id',
        'course_id',
        'status',
        'rating',
        'views',
    ];

    protected $casts = [
        'images' => 'array',
        'files' => 'array',
        'rating' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}