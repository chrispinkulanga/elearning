<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'bio',
        'avatar',
        'date_of_birth',
        'gender',
        'nationality',
        'location',
        'timezone',
        'website',
        'social_links',
        'languages',
        'current_position',
        'current_company',
        'expected_salary',
        'salary_currency',
        'employment_type',
        'work_availability',
        'is_public',
        'is_available_for_work',
        'last_updated_at',
    ];

    protected $casts = [
        'social_links' => 'array',
        'languages' => 'array',
        'date_of_birth' => 'date',
        'expected_salary' => 'decimal:2',
        'is_public' => 'boolean',
        'is_available_for_work' => 'boolean',
        'last_updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Career-related relationships removed as requested

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return null;
    }

    public function getPrimaryResumeAttribute()
    {
        return $this->documents()
            ->where('type', 'resume')
            ->where('is_primary', true)
            ->first();
    }
}