<?php
// app/Services/VideoService.php
namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;

class VideoService
{
    protected $ffmpeg;

    public function __construct()
    {
        // Initialize FFMpeg (you'll need to install ffmpeg-php package)
        // composer require php-ffmpeg/php-ffmpeg
        try {
            $this->ffmpeg = FFMpeg::create([
                'ffmpeg.binaries'  => '/usr/bin/ffmpeg',
                'ffprobe.binaries' => '/usr/bin/ffprobe',
                'timeout'          => 3600,
                'ffmpeg.threads'   => 12,
            ]);
        } catch (\Exception $e) {
            Log::warning('FFMpeg not available: ' . $e->getMessage());
        }
    }

    public function processVideo(string $videoPath)
    {
        $videoInfo = $this->getVideoInfo($videoPath);
        
        // Generate thumbnail
        $thumbnailPath = $this->generateThumbnail($videoPath);
        
        // Compress video if needed
        $compressedPath = $this->compressVideo($videoPath);
        
        // Generate different quality versions
        $qualityVersions = $this->generateQualityVersions($videoPath);

        return [
            'video_url' => $compressedPath ?? $videoPath,
            'duration' => $videoInfo['duration'],
            'thumbnail' => $thumbnailPath,
            'sizes' => $qualityVersions,
            'file_size' => Storage::size($compressedPath ?? $videoPath),
            'resolution' => $videoInfo['resolution'],
        ];
    }

    public function getVideoInfo(string $videoPath)
    {
        if (!$this->ffmpeg) {
            return [
                'duration' => 0,
                'resolution' => '0x0'
            ];
        }

        try {
            $video = $this->ffmpeg->open(Storage::path($videoPath));
            $duration = $video->getFFProbe()
                ->format(Storage::path($videoPath))
                ->get('duration');

            $videoStream = $video->getFFProbe()
                ->streams(Storage::path($videoPath))
                ->videos()
                ->first();

            $width = $videoStream->get('width');
            $height = $videoStream->get('height');

            return [
                'duration' => (int) $duration,
                'resolution' => $width . 'x' . $height,
                'width' => $width,
                'height' => $height,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get video info: ' . $e->getMessage());
            return [
                'duration' => 0,
                'resolution' => '0x0'
            ];
        }
    }

    public function generateThumbnail(string $videoPath, int $timeInSeconds = 10)
    {
        if (!$this->ffmpeg) {
            return null;
        }

        try {
            $video = $this->ffmpeg->open(Storage::path($videoPath));
            $thumbnailName = 'thumbnails/' . pathinfo($videoPath, PATHINFO_FILENAME) . '.jpg';
            $thumbnailPath = Storage::path($thumbnailName);

            $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds($timeInSeconds))
                ->save($thumbnailPath);

            return $thumbnailName;
        } catch (\Exception $e) {
            Log::error('Failed to generate thumbnail: ' . $e->getMessage());
            return null;
        }
    }

    public function compressVideo(string $videoPath, int $maxSizeMB = 100)
    {
        if (!$this->ffmpeg) {
            return null;
        }

        try {
            $currentSize = Storage::size($videoPath) / 1024 / 1024; // Size in MB
            
            if ($currentSize <= $maxSizeMB) {
                return $videoPath; // No compression needed
            }

            $video = $this->ffmpeg->open(Storage::path($videoPath));
            $format = new X264();
            $format->setKiloBitrate(1000); // Adjust bitrate for compression

            $compressedName = 'compressed/' . pathinfo($videoPath, PATHINFO_FILENAME) . '_compressed.mp4';
            $compressedPath = Storage::path($compressedName);

            $video->save($format, $compressedPath);

            return $compressedName;
        } catch (\Exception $e) {
            Log::error('Failed to compress video: ' . $e->getMessage());
            return null;
        }
    }

    public function generateQualityVersions(string $videoPath)
    {
        if (!$this->ffmpeg) {
            return [];
        }

        $qualities = [
            '720p' => ['width' => 1280, 'height' => 720, 'bitrate' => 1500],
            '480p' => ['width' => 854, 'height' => 480, 'bitrate' => 1000],
            '360p' => ['width' => 640, 'height' => 360, 'bitrate' => 600],
        ];

        $versions = [];

        foreach ($qualities as $quality => $settings) {
            try {
                $video = $this->ffmpeg->open(Storage::path($videoPath));
                $format = new X264();
                $format->setKiloBitrate($settings['bitrate']);

                $qualityName = 'quality/' . pathinfo($videoPath, PATHINFO_FILENAME) . '_' . $quality . '.mp4';
                $qualityPath = Storage::path($qualityName);

                $video->filters()
                    ->resize(new \FFMpeg\Coordinate\Dimension($settings['width'], $settings['height']))
                    ->synchronize();

                $video->save($format, $qualityPath);

                $versions[$quality] = [
                    'path' => $qualityName,
                    'size' => Storage::size($qualityName),
                    'resolution' => $settings['width'] . 'x' . $settings['height']
                ];
            } catch (\Exception $e) {
                Log::error("Failed to generate {$quality} version: " . $e->getMessage());
            }
        }

        return $versions;
    }

    public function extractAudio(string $videoPath)
    {
        if (!$this->ffmpeg) {
            return null;
        }

        try {
            $video = $this->ffmpeg->open(Storage::path($videoPath));
            $audioName = 'audio/' . pathinfo($videoPath, PATHINFO_FILENAME) . '.mp3';
            $audioPath = Storage::path($audioName);

            $video->save(new \FFMpeg\Format\Audio\Mp3(), $audioPath);

            return $audioName;
        } catch (\Exception $e) {
            Log::error('Failed to extract audio: ' . $e->getMessage());
            return null;
        }
    }

    public function validateVideo(string $videoPath)
    {
        $allowedFormats = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'];
        $maxSizeMB = 500; // 500MB limit
        $maxDurationMinutes = 120; // 2 hours limit

        $extension = strtolower(pathinfo($videoPath, PATHINFO_EXTENSION));
        $sizeInMB = Storage::size($videoPath) / 1024 / 1024;

        $errors = [];

        if (!in_array($extension, $allowedFormats)) {
            $errors[] = 'Invalid video format. Allowed formats: ' . implode(', ', $allowedFormats);
        }

        if ($sizeInMB > $maxSizeMB) {
            $errors[] = "Video size ({$sizeInMB}MB) exceeds maximum allowed size ({$maxSizeMB}MB)";
        }

        $videoInfo = $this->getVideoInfo($videoPath);
        $durationInMinutes = $videoInfo['duration'] / 60;

        if ($durationInMinutes > $maxDurationMinutes) {
            $errors[] = "Video duration ({$durationInMinutes} minutes) exceeds maximum allowed duration ({$maxDurationMinutes} minutes)";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'info' => $videoInfo
        ];
    }
}
