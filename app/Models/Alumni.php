<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alumni extends Model
{
    protected $fillable = [
        'user_id',
        'graduation_year',
        'degree',
        'major',
        'achievements',
        'current_position',
        'company',
        'linkedin_url',
        'status',
        'invited_at',
        'joined_at'
    ];

    protected $casts = [
        'invited_at' => 'datetime',
        'joined_at' => 'datetime',
    ];

    /**
     * Get the user that owns the alumni record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
