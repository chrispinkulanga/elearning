<?php
// app/Http/Controllers/Api/UserController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function profile()
    {
        $user = auth()->user()->load('roles');
        
        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'social_links' => 'nullable|array',
            'social_links.facebook' => 'nullable|url',
            'social_links.twitter' => 'nullable|url',
            'social_links.linkedin' => 'nullable|url',
            'social_links.instagram' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        auth()->user()->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'data' => auth()->user()->load('roles')
        ]);
    }

    public function uploadAvatar(Request $request)
    {
        $user = auth()->user();

        // Handle avatar removal (when no file is provided or empty string)
        if (!$request->hasFile('avatar') || $request->file('avatar') === null) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            $user->update(['avatar' => null]);

            return response()->json([
                'status' => 'success',
                'message' => 'Avatar removed successfully',
                'data' => ['avatar' => null]
            ]);
        }

        // Validate file upload
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Delete old avatar
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Upload new avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return response()->json([
            'status' => 'success',
            'message' => 'Avatar updated successfully',
            'data' => ['avatar' => $path]
        ]);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully'
        ]);
    }

    public function getCertificates(Request $request)
    {
        $certificates = auth()->user()->certificates()
            ->with('course:id,title')
            ->latest()
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'status' => 'success',
            'data' => $certificates
        ]);
    }

    public function getNotifications(Request $request)
    {
        $query = auth()->user()->notifications();

        if ($request->has('unread_only') && $request->unread_only) {
            $query->whereNull('read_at');
        }

        $notifications = $query->latest()
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data' => $notifications
        ]);
    }

    public function markNotificationRead($notificationId)
    {
        $notification = auth()->user()->notifications()->find($notificationId);

        if (!$notification) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read'
        ]);
    }

    public function markAllNotificationsRead()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read'
        ]);
    }

    public function getUnreadCount()
    {
        $count = auth()->user()->unreadNotifications()->count();

        return response()->json([
            'status' => 'success',
            'data' => ['count' => $count]
        ]);
    }

    public function deleteNotification($notificationId)
    {
        $notification = auth()->user()->notifications()->find($notificationId);

        if (!$notification) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification deleted'
        ]);
    }

    public function searchInstructors(Request $request)
    {
        $query = User::role('instructor')->where('status', 'active');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('bio', 'like', "%{$search}%");
            });
        }

        $instructors = $query->withCount('instructedCourses')
            ->latest()
            ->paginate($request->get('per_page', 12));

        return response()->json([
            'status' => 'success',
            'data' => $instructors
        ]);
    }

    public function downloadCertificate($certificateId)
    {
        $certificate = auth()->user()->certificates()->find($certificateId);

        if (!$certificate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Certificate not found'
            ], 404);
        }

        // Check if user has completed the course
        $enrollment = auth()->user()->enrollments()
            ->where('course_id', $certificate->course_id)
            ->where('status', 'completed')
            ->first();

        if (!$enrollment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Course not completed'
            ], 403);
        }

        // Generate certificate PDF
        $certificateService = app(CertificateService::class);
        $pdf = $certificateService->generateCertificatePDF($certificate);

        return $pdf->download('certificate_' . $certificate->id . '.pdf');
    }
}
