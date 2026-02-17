<?php
// app/Http/Controllers/Api/NotificationController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = auth()->user()->notifications();

        // Filter by read/unread status
        if ($request->has('unread_only') && $request->unread_only) {
            $query->whereNull('read_at');
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $notifications = $query->latest()
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data' => $notifications
        ]);
    }

    public function unreadCount()
    {
        $count = auth()->user()->unreadNotifications()->count();

        return response()->json([
            'status' => 'success',
            'data' => ['unread_count' => $count]
        ]);
    }

    public function markAsRead(DatabaseNotification $notification)
    {
        // Check if notification belongs to current user
        if ($notification->notifiable_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read'
        ]);
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read'
        ]);
    }

    public function destroy(DatabaseNotification $notification)
    {
        // Check if notification belongs to current user
        if ($notification->notifiable_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $notification->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification deleted'
        ]);
    }

    public function destroyAll()
    {
        auth()->user()->notifications()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications deleted'
        ]);
    }

    public function getNotificationSettings()
    {
        $user = auth()->user();
        
        // Get user notification preferences (you can store this in user profile or separate table)
        $settings = [
            'email_notifications' => $user->email_notifications ?? true,
            'course_updates' => $user->course_updates_notifications ?? true,
            'forum_replies' => $user->forum_replies_notifications ?? true,
            'quiz_results' => $user->quiz_results_notifications ?? true,
            'announcements' => $user->announcements_notifications ?? true,
            'marketing' => $user->marketing_notifications ?? false,
        ];

        return response()->json([
            'status' => 'success',
            'data' => $settings
        ]);
    }

    public function updateNotificationSettings(Request $request)
    {
        $user = auth()->user();

        $validatedData = $request->validate([
            'email_notifications' => 'boolean',
            'course_updates' => 'boolean',
            'forum_replies' => 'boolean',
            'quiz_results' => 'boolean',
            'announcements' => 'boolean',
            'marketing' => 'boolean',
        ]);

        // Update user notification preferences
        $user->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Notification settings updated successfully'
        ]);
    }
}









