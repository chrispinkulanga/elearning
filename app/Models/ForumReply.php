<?php
// app/Models/ForumReply.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'user_id',
        'topic_id',
        'parent_id',
        'upvotes',
        'likes_count',
    ];

    protected $casts = [
        'upvotes' => 'integer',
        'likes_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function topic()
    {
        return $this->belongsTo(ForumTopic::class, 'topic_id');
    }

    public function parent()
    {
        return $this->belongsTo(ForumReply::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ForumReply::class, 'parent_id');
    }

    // Accessor for likes_count to maintain compatibility
    public function getLikesCountAttribute()
    {
        return $this->upvotes;
    }

    // Upvotes relationship
    public function upvotes()
    {
        return $this->hasMany(ForumReplyUpvote::class, 'forum_reply_id');
    }

    // Likes relationship
    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }
}