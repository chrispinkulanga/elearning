<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'page_id',
        'widget_id',
        'completed',
        'completed_at'
    ];

    protected $casts = [
        'completed' => 'boolean',
        'completed_at' => 'datetime'
    ];

    /**
     * Get the user that owns the progress record
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the course that this progress belongs to
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the page that this progress belongs to
     */
    public function page()
    {
        return $this->belongsTo(CoursePage::class);
    }

    /**
     * Get the widget that this progress belongs to
     */
    public function widget()
    {
        return $this->belongsTo(CourseWidget::class);
    }
}
