<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDocument extends Model
{
    use HasFactory;

    protected $table = 'user_documents';

    protected $fillable = [
        'user_id',
        'name',
        'filename',
        'path',
        'mime_type',
        'size',
        'type',
        'title',
        'description',
        'is_primary',
        'is_public',
        'metadata',
        'uploaded_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_primary' => 'boolean',
        'is_public' => 'boolean',
        'uploaded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }

    public function getSizeFormattedAttribute()
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFileExtensionAttribute()
    {
        return pathinfo($this->name, PATHINFO_EXTENSION);
    }

    public function scopeResumes($query)
    {
        return $query->where('type', 'resume');
    }

    public function scopeCvs($query)
    {
        return $query->where('type', 'cv');
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
}