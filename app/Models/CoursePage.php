<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoursePage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'order_index',
        'content_type',
        'is_published',
        'is_preview',
        'settings'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_preview' => 'boolean',
        'settings' => 'array'
    ];

    /**
     * Relationships
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function widgets()
    {
        return $this->hasMany(CourseWidget::class, 'page_id')->orderBy('order_index');
    }

    public function activeWidgets()
    {
        return $this->hasMany(CourseWidget::class, 'page_id')
                    ->where('is_active', true)
                    ->orderBy('order_index');
    }

    /**
     * Scopes
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopePreview($query)
    {
        return $query->where('is_preview', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('content_type', $type);
    }

    /**
     * Helper methods
     */
    public function getTotalWidgetsAttribute()
    {
        return $this->widgets()->count();
    }

    public function getActiveWidgetsCountAttribute()
    {
        return $this->activeWidgets()->count();
    }

    public function hasWidgets()
    {
        return $this->widgets()->exists();
    }

    public function isPublished()
    {
        return $this->is_published;
    }

    public function isPreview()
    {
        return $this->is_preview;
    }

    public function canBePublished()
    {
        return $this->activeWidgets()->count() > 0;
    }

    public function publish()
    {
        if ($this->canBePublished()) {
            $this->update(['is_published' => true]);
            return true;
        }
        return false;
    }

    public function unpublish()
    {
        $this->update(['is_published' => false]);
    }

    public function duplicate()
    {
        $newPage = $this->replicate();
        $newPage->title = $this->title . ' (Copy)';
        $newPage->is_published = false;
        $newPage->is_preview = false;
        $newPage->order_index = $this->course->pages()->count();
        $newPage->save();

        // Duplicate widgets
        foreach ($this->widgets as $widget) {
            $newWidget = $widget->replicate();
            $newWidget->page_id = $newPage->id;
            $newWidget->save();
        }

        return $newPage;
    }
}
