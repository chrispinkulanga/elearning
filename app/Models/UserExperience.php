<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserExperience extends Model
{
    use HasFactory;

    protected $table = 'user_experiences';

    protected $fillable = [
        'user_id',
        'company_name',
        'position',
        'description',
        'location',
        'start_date',
        'end_date',
        'is_current',
        'employment_type',
        'achievements',
        'skills_used',
        'company_website',
        'company_logo',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'achievements' => 'array',
        'skills_used' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDurationAttribute()
    {
        $start = $this->start_date;
        $end = $this->is_current ? now() : $this->end_date;
        
        if (!$start || !$end) {
            return null;
        }
        
        $years = $start->diffInYears($end);
        $months = $start->diffInMonths($end) % 12;
        
        $duration = [];
        if ($years > 0) {
            $duration[] = $years . ' ' . ($years === 1 ? 'year' : 'years');
        }
        if ($months > 0) {
            $duration[] = $months . ' ' . ($months === 1 ? 'month' : 'months');
        }
        
        return implode(' ', $duration);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopePast($query)
    {
        return $query->where('is_current', false);
    }
}