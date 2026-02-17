<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseWidget extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'page_id',
        'widget_type',
        'widget_data',
        'order_index',
        'is_active',
        'settings'
    ];

    protected $casts = [
        'widget_data' => 'array',
        'is_active' => 'boolean',
        'settings' => 'array'
    ];

    /**
     * Widget types constants
     */
    const TYPE_TEXT = 'text';
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_MCQ = 'mcq';
    const TYPE_POLL = 'poll';
    const TYPE_FILE = 'file';
    const TYPE_CODE = 'code';
    const TYPE_EMBED = 'embed';
    const TYPE_QUIZ = 'quiz';
    const TYPE_ASSIGNMENT = 'assignment';

    /**
     * Get all available widget types
     */
    public static function getAvailableTypes()
    {
        return [
            self::TYPE_TEXT => 'Text Content',
            self::TYPE_IMAGE => 'Image',
            self::TYPE_VIDEO => 'Video',
            self::TYPE_MCQ => 'Multiple Choice Question',
            self::TYPE_POLL => 'Poll',
            self::TYPE_FILE => 'File Download',
            self::TYPE_CODE => 'Code Block',
            self::TYPE_EMBED => 'Embedded Content',
            self::TYPE_QUIZ => 'Quiz',
            self::TYPE_ASSIGNMENT => 'Assignment'
        ];
    }

    /**
     * Relationships
     */
    public function page()
    {
        return $this->belongsTo(CoursePage::class, 'page_id');
    }

    public function course()
    {
        return $this->hasOneThrough(Course::class, CoursePage::class, 'id', 'id', 'page_id', 'course_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('widget_type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index');
    }

    /**
     * Helper methods
     */
    public function isTextWidget()
    {
        return $this->widget_type === self::TYPE_TEXT;
    }

    public function isImageWidget()
    {
        return $this->widget_type === self::TYPE_IMAGE;
    }

    public function isVideoWidget()
    {
        return $this->widget_type === self::TYPE_VIDEO;
    }

    public function isMCQWidget()
    {
        return $this->widget_type === self::TYPE_MCQ;
    }

    public function isPollWidget()
    {
        return $this->widget_type === self::TYPE_POLL;
    }

    public function isFileWidget()
    {
        return $this->widget_type === self::TYPE_FILE;
    }

    public function isCodeWidget()
    {
        return $this->widget_type === self::TYPE_CODE;
    }

    public function isEmbedWidget()
    {
        return $this->widget_type === self::TYPE_EMBED;
    }

    public function isQuizWidget()
    {
        return $this->widget_type === self::TYPE_QUIZ;
    }

    public function isAssignmentWidget()
    {
        return $this->widget_type === self::TYPE_ASSIGNMENT;
    }

    /**
     * Widget-specific data getters
     */
    public function getTextContent()
    {
        if ($this->isTextWidget()) {
            return $this->widget_data['content'] ?? '';
        }
        return '';
    }

    public function getImageData()
    {
        if ($this->isImageWidget()) {
            return [
                'url' => $this->widget_data['url'] ?? '',
                'alt' => $this->widget_data['alt'] ?? '',
                'caption' => $this->widget_data['caption'] ?? '',
                'width' => $this->widget_data['width'] ?? null,
                'height' => $this->widget_data['height'] ?? null
            ];
        }
        return null;
    }

    public function getVideoData()
    {
        if ($this->isVideoWidget()) {
            return [
                'url' => $this->widget_data['url'] ?? '',
                'title' => $this->widget_data['title'] ?? '',
                'description' => $this->widget_data['description'] ?? '',
                'duration' => $this->widget_data['duration'] ?? null,
                'provider' => $this->widget_data['provider'] ?? 'youtube'
            ];
        }
        return null;
    }

    public function getMCQData()
    {
        if ($this->isMCQWidget()) {
            return [
                'question' => $this->widget_data['question'] ?? '',
                'options' => $this->widget_data['options'] ?? [],
                'correct_answer' => $this->widget_data['correct_answer'] ?? null,
                'explanation' => $this->widget_data['explanation'] ?? '',
                'is_multiple_choice' => $this->widget_data['is_multiple_choice'] ?? false
            ];
        }
        return null;
    }

    public function getPollData()
    {
        if ($this->isPollWidget()) {
            return [
                'question' => $this->widget_data['question'] ?? '',
                'options' => $this->widget_data['options'] ?? [],
                'is_multiple_choice' => $this->widget_data['is_multiple_choice'] ?? false,
                'is_active' => $this->widget_data['is_active'] ?? true,
                'expires_at' => $this->widget_data['expires_at'] ?? null
            ];
        }
        return null;
    }

    public function getFileData()
    {
        if ($this->isFileWidget()) {
            return [
                'filename' => $this->widget_data['filename'] ?? '',
                'url' => $this->widget_data['url'] ?? '',
                'size' => $this->widget_data['size'] ?? null,
                'type' => $this->widget_data['type'] ?? '',
                'description' => $this->widget_data['description'] ?? ''
            ];
        }
        return null;
    }

    public function getCodeData()
    {
        if ($this->isCodeWidget()) {
            return [
                'code' => $this->widget_data['code'] ?? '',
                'language' => $this->widget_data['language'] ?? 'text',
                'title' => $this->widget_data['title'] ?? '',
                'description' => $this->widget_data['description'] ?? ''
            ];
        }
        return null;
    }

    public function getEmbedData()
    {
        if ($this->isEmbedWidget()) {
            return [
                'embed_code' => $this->widget_data['embed_code'] ?? '',
                'title' => $this->widget_data['title'] ?? '',
                'description' => $this->widget_data['description'] ?? '',
                'width' => $this->widget_data['width'] ?? '100%',
                'height' => $this->widget_data['height'] ?? '400px'
            ];
        }
        return null;
    }

    /**
     * Validation methods
     */
    public function isValid()
    {
        switch ($this->widget_type) {
            case self::TYPE_TEXT:
                return !empty($this->widget_data['content'] ?? '');
            case self::TYPE_IMAGE:
                return !empty($this->widget_data['url'] ?? '');
            case self::TYPE_VIDEO:
                return !empty($this->widget_data['url'] ?? '');
            case self::TYPE_MCQ:
                return !empty($this->widget_data['question'] ?? '') && 
                       !empty($this->widget_data['options'] ?? []);
            case self::TYPE_POLL:
                return !empty($this->widget_data['question'] ?? '') && 
                       !empty($this->widget_data['options'] ?? []);
            case self::TYPE_FILE:
                return !empty($this->widget_data['url'] ?? '');
            case self::TYPE_CODE:
                return !empty($this->widget_data['code'] ?? '');
            case self::TYPE_EMBED:
                return !empty($this->widget_data['embed_code'] ?? '');
            default:
                return false;
        }
    }

    public function duplicate()
    {
        $newWidget = $this->replicate();
        $newWidget->order_index = $this->page->widgets()->count();
        $newWidget->save();
        return $newWidget;
    }
}
