<?php
// app/Models/Course.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'short_description',
        'thumbnail',
        'preview_video',
        'price',
        'discounted_price',
        'level',
        'tags',
        'status',
        'is_free',
        'is_featured',
        'access_type',
        'access_days',
        'duration_hours',
        'requirements',
        'outcomes',
        'instructor_id',
        'category_id',
    ];

    protected $casts = [
        'tags' => 'array',
        'requirements' => 'array',
        'outcomes' => 'array',
        'is_free' => 'boolean',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
        'discounted_price' => 'decimal:2',
    ];

    // Automatically generate slug
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($course) {
            if (empty($course->slug)) {
                $course->slug = Str::slug($course->title);
            }
        });
    }

    // Relationships
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class)->orderBy('sort_order');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments')
                    ->withPivot(['amount_paid', 'status', 'enrolled_at', 'expires_at', 'progress_percentage'])
                    ->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function forums()
    {
        return $this->hasMany(Forum::class);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function studentProducts()
    {
        return $this->hasMany(StudentProduct::class);
    }

    // NEW: Course Builder relationships
    public function pages()
    {
        return $this->hasMany(CoursePage::class)->orderBy('order_index');
    }

    public function publishedPages()
    {
        return $this->hasMany(CoursePage::class)->published()->ordered();
    }

    public function previewPages()
    {
        return $this->hasMany(CoursePage::class)->preview()->ordered();
    }

    public function totalPages()
    {
        return $this->pages()->count();
    }

    public function totalPublishedPages()
    {
        return $this->publishedPages()->count();
    }

    public function totalWidgets()
    {
        return $this->pages()->withCount('widgets')->get()->sum('widgets_count');
    }

    // Helper methods
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->where('status', 'approved')->avg('rating') ?: 0;
    }

    public function getTotalEnrollmentsAttribute()
    {
        return $this->enrollments()->where('status', 'active')->count();
    }

    public function getCurrentPriceAttribute()
    {
        return $this->discounted_price ?: $this->price;
    }

    public function getTotalDurationAttribute()
    {
        return $this->lessons()->sum('video_duration');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }

    public function scopeByInstructor($query, $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    // Check if user owns this course (is instructor)
    public function isOwnedBy($userId)
    {
        return $this->instructor_id == $userId;
    }

    // Get total sections count
    public function getTotalSectionsAttribute()
    {
        return $this->sections()->count();
    }

    // Get total lessons count (through sections)
    public function getTotalLessonsAttribute()
    {
        return $this->sections()->withCount('lessons')->get()->sum('lessons_count');
    }
}