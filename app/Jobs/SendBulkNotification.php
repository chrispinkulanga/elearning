<?php
// app/Jobs/SendBulkNotification.php
namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SendBulkNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userIds;
    public $notificationClass;
    public $notificationData;

    public function __construct(array $userIds, string $notificationClass, array $notificationData = [])
    {
        $this->userIds = $userIds;
        $this->notificationClass = $notificationClass;
        $this->notificationData = $notificationData;
    }

    public function handle()
    {
        // Process users in chunks to avoid memory issues
        collect($this->userIds)->chunk(100)->each(function ($chunk) {
            $users = User::whereIn('id', $chunk)->get();
            
            foreach ($users as $user) {
                try {
                    // Create notification instance
                    $notification = new $this->notificationClass(...array_values($this->notificationData));
                    
                    // Send notification
                    $user->notify($notification);
                    
                } catch (\Exception $e) {
                    \Log::error('Failed to send bulk notification to user', [
                        'user_id' => $user->id,
                        'notification_class' => $this->notificationClass,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });
    }

    public function failed(\Exception $exception)
    {
        \Log::error('Bulk notification job failed', [
            'user_count' => count($this->userIds),
            'notification_class' => $this->notificationClass,
            'error' => $exception->getMessage()
        ]);
    }
}
