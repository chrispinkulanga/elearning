<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSkill extends Model
{
    use HasFactory;

    protected $table = 'user_skills';

    protected $fillable = [
        'user_id',
        'name',
        'category',
        'proficiency_level',
        'years_of_experience',
        'description',
        'endorsements',
        'is_verified',
        'last_used_at',
    ];

    protected $casts = [
        'endorsements' => 'array',
        'is_verified' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeTechnical($query)
    {
        return $query->where('category', 'technical');
    }

    public function scopeSoft($query)
    {
        return $query->where('category', 'soft');
    }

    public function scopeLanguages($query)
    {
        return $query->where('category', 'language');
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function getProficiencyLevelColorAttribute()
    {
        return match($this->proficiency_level) {
            'beginner' => 'green',
            'intermediate' => 'yellow',
            'advanced' => 'orange',
            'expert' => 'red',
            default => 'gray'
        };
    }
}