<?php
// app/Http/Controllers/Api/AnnouncementController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnnouncementController extends Controller
{
    public function index(Course $course)
    {
        // Check if user has access to this course
        if (!$this->hasAccess($course)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied'
            ], 403);
        }

        $announcements = $course->announcements()
            ->with('user:id,name,avatar')
            ->where('is_published', true)
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $announcements
        ]);
    }

    public function show(Course $course, Announcement $announcement)
    {
        // Check if announcement belongs to course
        if ($announcement->course_id !== $course->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Announcement not found in this course'
            ], 404);
        }

        // Check access
        if (!$this->hasAccess($course)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied'
            ], 403);
        }

        $announcement->load('user:id,name,avatar');

        return response()->json([
            'status' => 'success',
            'data' => $announcement
        ]);
    }

    public function store(Request $request, Course $course)
    {
        // Check if user can create announcements for this course
        if (!auth()->user()->hasRole('admin') && $course->instructor_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_published' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $announcement = Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'course_id' => $course->id,
            'user_id' => auth()->id(),
            'is_published' => $request->get('is_published', true),
        ]);

        // Send notifications to enrolled students if published
        if ($announcement->is_published) {
            $this->notifyEnrolledStudents($course, $announcement);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Announcement created successfully',
            'data' => $announcement->load('user:id,name,avatar')
        ], 201);
    }

    public function update(Request $request, Course $course, Announcement $announcement)
    {
        // Check if announcement belongs to course
        if ($announcement->course_id !== $course->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Announcement not found in this course'
            ], 404);
        }

        // Check if user can update this announcement
        if (!auth()->user()->hasRole('admin') && $announcement->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_published' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $wasPublished = $announcement->is_published;
        $announcement->update($request->all());

        // Send notifications if announcement is newly published
        if (!$wasPublished && $announcement->is_published) {
            $this->notifyEnrolledStudents($course, $announcement);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Announcement updated successfully',
            'data' => $announcement->load('user:id,name,avatar')
        ]);
    }

    public function destroy(Course $course, Announcement $announcement)
    {
        // Check if announcement belongs to course
        if ($announcement->course_id !== $course->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Announcement not found in this course'
            ], 404);
        }

        // Check if user can delete this announcement
        if (!auth()->user()->hasRole('admin') && $announcement->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $announcement->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Announcement deleted successfully'
        ]);
    }

    public function getInstructorAnnouncements(Request $request)
    {
        // Get all announcements created by the current instructor
        $announcements = Announcement::where('user_id', auth()->id())
            ->with(['course:id,title'])
            ->latest()
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $announcements
        ]);
    }

    public function togglePublishStatus(Course $course, Announcement $announcement)
    {
        // Check if announcement belongs to course
        if ($announcement->course_id !== $course->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Announcement not found in this course'
            ], 404);
        }

        // Check if user can update this announcement
        if (!auth()->user()->hasRole('admin') && $announcement->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $announcement->update([
            'is_published' => !$announcement->is_published
        ]);

        // Send notifications if announcement is newly published
        if ($announcement->is_published) {
            $this->notifyEnrolledStudents($course, $announcement);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Announcement status updated successfully',
            'data' => $announcement
        ]);
    }

    private function hasAccess($course)
    {
        return auth()->check() && (
            auth()->user()->isEnrolledIn($course->id) ||
            $course->instructor_id === auth()->id() ||
            auth()->user()->hasRole('admin')
        );
    }

    private function notifyEnrolledStudents($course, $announcement)
    {
        // Get all enrolled students
        $enrolledStudents = $course->students()->where('enrollments.status', 'active')->get();

        foreach ($enrolledStudents as $student) {
            // Send notification to each student
            $student->notify(new \App\Notifications\NewAnnouncement($announcement));
        }
    }
}