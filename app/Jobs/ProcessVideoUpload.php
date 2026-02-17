<?php
// app/Jobs/ProcessVideoUpload.php
namespace App\Jobs;

use App\Models\Lesson;
use App\Services\VideoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessVideoUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $lesson;
    public $videoPath;

    public function __construct(Lesson $lesson, string $videoPath)
    {
        $this->lesson = $lesson;
        $this->videoPath = $videoPath;
    }

    public function handle(VideoService $videoService)
    {
        try {
            // Process video (compress, generate thumbnails, etc.)
            $processedData = $videoService->processVideo($this->videoPath);

            // Update lesson with processed video data
            $this->lesson->update([
                'video_url' => $processedData['video_url'],
                'video_duration' => $processedData['duration'],
                'video_thumbnail' => $processedData['thumbnail'],
                'video_sizes' => $processedData['sizes'], // Different quality versions
                'processing_status' => 'completed'
            ]);

            // Delete original uploaded file if different from processed
            if ($this->videoPath !== $processedData['video_url']) {
                Storage::delete($this->videoPath);
            }

        } catch (\Exception $e) {
            // Mark as failed
            $this->lesson->update([
                'processing_status' => 'failed',
                'processing_error' => $e->getMessage()
            ]);

            // Clean up uploaded file
            Storage::delete($this->videoPath);

            throw $e;
        }
    }

    public function failed(\Exception $exception)
    {
        // Handle failed job
        $this->lesson->update([
            'processing_status' => 'failed',
            'processing_error' => $exception->getMessage()
        ]);

        // Clean up
        Storage::delete($this->videoPath);

        \Log::error('Video processing failed', [
            'lesson_id' => $this->lesson->id,
            'video_path' => $this->videoPath,
            'error' => $exception->getMessage()
        ]);
    }
}