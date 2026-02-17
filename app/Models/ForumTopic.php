<?php
// app/Models/ForumTopic.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumTopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'category',
        'user_id',
        'forum_id',
        'is_pinned',
        'is_locked',
        'views',
        'tags',
        'reports_count',
        'likes_count',
        'poll_question',
        'poll_options',
        'poll_is_multiple_choice',
        'poll_is_active',
        'poll_expires_at',
        'attachments',
        'attachments_count',
        'is_draft', // Add draft status
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_locked' => 'boolean',
        'tags' => 'array',
        'reports_count' => 'integer',
        'likes_count' => 'integer',
        'poll_options' => 'array',
        'poll_is_multiple_choice' => 'boolean',
        'poll_is_active' => 'boolean',
        'poll_expires_at' => 'datetime',
        'attachments' => 'array',
        'attachments_count' => 'integer',
        'is_draft' => 'boolean', // Add draft status
    ];

    // Accessor for views_count to maintain compatibility
    public function getViewsCountAttribute()
    {
        return $this->views;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }

    public function replies()
    {
        return $this->hasMany(ForumReply::class, 'topic_id');
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    // Poll methods
    public function hasPoll()
    {
        return !empty($this->poll_question);
    }

    public function isPollActive()
    {
        if (!$this->poll_is_active) {
            return false;
        }
        
        if ($this->poll_expires_at && $this->poll_expires_at->isPast()) {
            return false;
        }
        
        return true;
    }

    public function getPollOptions()
    {
        return $this->poll_options ?? [];
    }

    // Attachment methods
    public function hasAttachments()
    {
        return $this->attachments_count > 0;
    }

    public function getAttachments()
    {
        return $this->attachments ?? [];
    }

    public function addAttachment($attachmentData)
    {
        $attachments = $this->attachments ?? [];
        $attachments[] = $attachmentData;
        $this->attachments = $attachments;
        $this->attachments_count = count($attachments);
        $this->save();
    }

    public function removeAttachment($index)
    {
        $attachments = $this->attachments ?? [];
        if (isset($attachments[$index])) {
            unset($attachments[$index]);
            $attachments = array_values($attachments); // Re-index array
            $this->attachments = $attachments;
            $this->attachments_count = count($attachments);
            $this->save();
        }
    }
}