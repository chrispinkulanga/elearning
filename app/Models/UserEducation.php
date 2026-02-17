<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEducation extends Model
{
    use HasFactory;

    protected $table = 'user_educations';

    protected $fillable = [
        'user_id',
        'institution_name',
        'degree',
        'field_of_study',
        'description',
        'location',
        'start_date',
        'end_date',
        'is_current',
        'gpa',
        'gpa_scale',
        'activities',
        'achievements',
        'institution_website',
        'institution_logo',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'gpa' => 'decimal:2',
        'activities' => 'array',
        'achievements' => 'array',
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

    public function getGpaFormattedAttribute()
    {
        if (!$this->gpa) {
            return null;
        }
        
        return $this->gpa . '/' . ($this->gpa_scale ?? '4.0');
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeCompleted($query)
    {
        return $query->where('is_current', false);
    }
}