<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopicAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'user_id',
        'filename',
        'original_filename',
        'file_path',
        'file_size',
        'mime_type',
        'file_type',
        'is_image',
        'is_video',
        'thumbnail_path',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_image' => 'boolean',
        'is_video' => 'boolean',
    ];

    public function topic()
    {
        return $this->belongsTo(ForumTopic::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFileTypeCategoryAttribute()
    {
        if ($this->is_image) return 'image';
        if ($this->is_video) return 'video';
        if (in_array($this->mime_type, ['application/pdf'])) return 'pdf';
        if (in_array($this->mime_type, ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) return 'document';
        if (in_array($this->mime_type, ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])) return 'spreadsheet';
        if (in_array($this->mime_type, ['application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'])) return 'presentation';
        return 'other';
    }
}
