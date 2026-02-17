<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'category',
        'template_data',
        'thumbnail',
        'is_active',
        'is_featured',
        'usage_count',
        'created_by'
    ];

    protected $casts = [
        'template_data' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'usage_count' => 'integer'
    ];

    /**
     * Template categories
     */
    const CATEGORY_GENERAL = 'general';
    const CATEGORY_TECHNICAL = 'technical';
    const CATEGORY_BUSINESS = 'business';
    const CATEGORY_CREATIVE = 'creative';
    const CATEGORY_LANGUAGE = 'language';
    const CATEGORY_SCIENCE = 'science';
    const CATEGORY_MATH = 'math';
    const CATEGORY_HISTORY = 'history';
    const CATEGORY_LITERATURE = 'literature';

    /**
     * Get all available categories
     */
    public static function getAvailableCategories()
    {
        return [
            self::CATEGORY_GENERAL => 'General',
            self::CATEGORY_TECHNICAL => 'Technical',
            self::CATEGORY_BUSINESS => 'Business',
            self::CATEGORY_CREATIVE => 'Creative',
            self::CATEGORY_LANGUAGE => 'Language',
            self::CATEGORY_SCIENCE => 'Science',
            self::CATEGORY_MATH => 'Mathematics',
            self::CATEGORY_HISTORY => 'History',
            self::CATEGORY_LITERATURE => 'Literature'
        ];
    }

    /**
     * Relationships
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopePopular($query)
    {
        return $query->orderBy('usage_count', 'desc');
    }

    /**
     * Helper methods
     */
    public function getCategoryNameAttribute()
    {
        $categories = self::getAvailableCategories();
        return $categories[$this->category] ?? $this->category;
    }

    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function isFeatured()
    {
        return $this->is_featured;
    }

    public function getTemplateStructure()
    {
        return $this->template_data['structure'] ?? [];
    }

    public function getTemplatePages()
    {
        return $this->template_data['pages'] ?? [];
    }

    public function getTemplateSettings()
    {
        return $this->template_data['settings'] ?? [];
    }

    /**
     * Apply template to a course
     */
    public function applyToCourse(Course $course)
    {
        $pages = $this->getTemplatePages();
        
        foreach ($pages as $pageData) {
            $page = $course->pages()->create([
                'title' => $pageData['title'] ?? 'Untitled Page',
                'description' => $pageData['description'] ?? null,
                'content_type' => $pageData['content_type'] ?? 'lesson',
                'order_index' => $pageData['order_index'] ?? 0,
                'is_published' => false,
                'is_preview' => $pageData['is_preview'] ?? false,
                'settings' => $pageData['settings'] ?? []
            ]);

            // Create widgets for this page
            if (isset($pageData['widgets'])) {
                foreach ($pageData['widgets'] as $widgetData) {
                    $page->widgets()->create([
                        'widget_type' => $widgetData['type'],
                        'widget_data' => $widgetData['data'] ?? [],
                        'order_index' => $widgetData['order_index'] ?? 0,
                        'is_active' => true,
                        'settings' => $widgetData['settings'] ?? []
                    ]);
                }
            }
        }

        // Increment usage count
        $this->incrementUsage();

        return $course;
    }

    /**
     * Create a new template from existing course
     */
    public static function createFromCourse(Course $course, $name, $description = null, $category = 'general')
    {
        $templateData = [
            'structure' => [
                'total_pages' => $course->pages()->count(),
                'total_widgets' => $course->pages()->withCount('widgets')->get()->sum('widgets_count'),
                'created_at' => now()
            ],
            'pages' => [],
            'settings' => [
                'course_title' => $course->title,
                'course_description' => $course->description,
                'level' => $course->level,
                'category' => $course->category->name ?? null
            ]
        ];

        // Extract pages and widgets
        foreach ($course->pages()->with('widgets')->ordered()->get() as $page) {
            $pageData = [
                'title' => $page->title,
                'description' => $page->description,
                'content_type' => $page->content_type,
                'order_index' => $page->order_index,
                'is_preview' => $page->is_preview,
                'settings' => $page->settings,
                'widgets' => []
            ];

            foreach ($page->widgets()->ordered()->get() as $widget) {
                $pageData['widgets'][] = [
                    'type' => $widget->widget_type,
                    'data' => $widget->widget_data,
                    'order_index' => $widget->order_index,
                    'settings' => $widget->settings
                ];
            }

            $templateData['pages'][] = $pageData;
        }

        return self::create([
            'name' => $name,
            'description' => $description,
            'category' => $category,
            'template_data' => $templateData,
            'created_by' => auth()->id()
        ]);
    }
}
