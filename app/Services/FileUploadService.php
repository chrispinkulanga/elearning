<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class FileUploadService
{
    protected $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    protected $allowedVideoTypes = [
        'video/mp4',
        'video/webm',
        'video/ogg',
        'video/quicktime',
        'video/x-msvideo'
    ];
    protected $allowedDocumentTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'text/csv'
    ];
    
    protected $maxFileSize = 10 * 1024 * 1024; // 10MB
    protected $maxImageSize = 5 * 1024 * 1024; // 5MB

    public function uploadFile(UploadedFile $file, $directory = 'attachments')
    {
        // Validate file size
        if ($file->getSize() > $this->maxFileSize) {
            throw new \Exception('File size exceeds maximum limit of ' . $this->formatBytes($this->maxFileSize));
        }

        // Validate file type
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, array_merge($this->allowedImageTypes, $this->allowedVideoTypes, $this->allowedDocumentTypes))) {
            throw new \Exception('File type not allowed');
        }

        // Generate unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs($directory, $filename, 'public');

        // Check if it's an image and create thumbnail
        $thumbnailPath = null;
        $isImage = in_array($mimeType, $this->allowedImageTypes);
        $isVideo = in_array($mimeType, $this->allowedVideoTypes);
        
        if ($isImage) {
            $thumbnailPath = $this->createThumbnail($file, $directory);
        }

        return [
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $mimeType,
            'file_type' => $file->getClientOriginalExtension(),
            'is_image' => $isImage,
            'is_video' => $isVideo,
            'thumbnail_path' => $thumbnailPath,
        ];
    }

    protected function createThumbnail(UploadedFile $file, $directory)
    {
        try {
            // Check if Intervention Image is available
            if (!class_exists('Intervention\Image\Laravel\Facades\Image')) {
                \Log::warning('Intervention Image Laravel not available, skipping thumbnail creation');
                return null;
            }
            
            $image = Image::read($file);
            $image->scaleDown(300, 300);

            $thumbnailFilename = 'thumb_' . Str::uuid() . '.jpg';
            $thumbnailPath = $directory . '/' . $thumbnailFilename;
            
            Storage::disk('public')->put($thumbnailPath, $image->toJpeg(80));
            
            return $thumbnailPath;
        } catch (\Exception $e) {
            \Log::error('Failed to create thumbnail: ' . $e->getMessage());
            return null;
        }
    }

    public function deleteFile($filePath, $thumbnailPath = null)
    {
        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        if ($thumbnailPath && Storage::disk('public')->exists($thumbnailPath)) {
            Storage::disk('public')->delete($thumbnailPath);
        }
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    public function getAllowedTypes()
    {
        return [
            'images' => $this->allowedImageTypes,
            'videos' => $this->allowedVideoTypes,
            'documents' => $this->allowedDocumentTypes,
            'max_size' => $this->maxFileSize,
            'max_image_size' => $this->maxImageSize,
        ];
    }
}
