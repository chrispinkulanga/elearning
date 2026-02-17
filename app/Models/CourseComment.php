<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseComment extends Model
{
    protected $fillable = [
        'course_id',
        'user_id',
        'parent_id',
        'content',
        'is_instructor_reply',
        'is_approved'
    ];

    protected $casts = [
        'is_instructor_reply' => 'boolean',
        'is_approved' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CourseComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(CourseComment::class, 'parent_id')->orderBy('created_at');
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeReplies($query)
    {
        return $query->whereNotNull('parent_id');
    }
}
