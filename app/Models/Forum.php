<?php
// app/Models/Forum.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forum extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'course_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function isStandalone()
    {
        return is_null($this->course_id);
    }

    public function topics()
    {
        return $this->hasMany(ForumTopic::class);
    }

    public function replies()
    {
        return $this->hasManyThrough(ForumReply::class, ForumTopic::class);
    }
}