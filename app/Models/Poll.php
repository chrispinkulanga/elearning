<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'question',
        'is_multiple_choice',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'is_multiple_choice' => 'boolean',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function topic()
    {
        return $this->belongsTo(ForumTopic::class);
    }

    public function options()
    {
        return $this->hasMany(PollOption::class);
    }

    public function votes()
    {
        return $this->hasMany(PollVote::class);
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getTotalVotesAttribute()
    {
        return $this->votes()->count();
    }
}
