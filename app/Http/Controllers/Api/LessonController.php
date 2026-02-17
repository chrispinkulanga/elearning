<?php
// app/Http/Controllers/Api/LessonController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Section;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LessonController extends Controller
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

        $lessons = $course->lessons()
            ->with(['userProgress' => function ($query) {
                $query->where('user_id', auth()->id());
            }])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $lessons
        ]);
    }

    public function show(Course $course, Lesson $lesson)
    {
        // Check if lesson belongs to course
        if ($lesson->course_id !== $course->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lesson not found in this course'
            ], 404);
        }

        // Check access
        if (!$this->hasAccess($course) && !$lesson->is_preview) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied'
            ], 403);
        }

        $lesson->load(['userProgress' => function ($query) {
            $query->where('user_id', auth()->id());
        }]);

        return response()->json([
            'status' => 'success',
            'data' => $lesson
        ]);
    }

    public function store(Request $request, Course $course)
    {
        // Check if user can add lessons to this course
        if (!auth()->user()->hasRole('admin') && $course->instructor_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:video,text,quiz,assignment',
            'video_url' => 'required_if:type,video|nullable|url',
            'video_duration' => 'nullable|integer|min:0',
            'content' => 'required_if:type,text|nullable|string',
            'attachments' => 'nullable|array',
            'is_preview' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $lessonData = $request->all();
        $lessonData['course_id'] = $course->id;

        // Set sort order if not provided
        if (!isset($lessonData['sort_order'])) {
            $lessonData['sort_order'] = $course->lessons()->max('sort_order') + 1;
        }

        $lesson = Lesson::create($lessonData);

        return response()->json([
            'status' => 'success',
            'message' => 'Lesson created successfully',
            'data' => $lesson
        ], 201);
    }

    public function update(Request $request, Course $course, Lesson $lesson)
    {
        // Check if lesson belongs to course
        if ($lesson->course_id !== $course->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lesson not found in this course'
            ], 404);
        }

        // Check if user can update this lesson
        if (!auth()->user()->hasRole('admin') && $course->instructor_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:video,text,quiz,assignment',
            'video_url' => 'required_if:type,video|nullable|url',
            'video_duration' => 'nullable|integer|min:0',
            'content' => 'required_if:type,text|nullable|string',
            'attachments' => 'nullable|array',
            'is_preview' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $lesson->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Lesson updated successfully',
            'data' => $lesson
        ]);
    }

    public function destroy(Course $course, Lesson $lesson)
    {
        // Check if lesson belongs to course
        if ($lesson->course_id !== $course->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lesson not found in this course'
            ], 404);
        }

        // Check if user can delete this lesson
        if (!auth()->user()->hasRole('admin') && $course->instructor_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $lesson->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Lesson deleted successfully'
        ]);
    }

    public function markComplete(Request $request, Course $course, Lesson $lesson)
    {
        // Check if lesson belongs to course
        if ($lesson->course_id !== $course->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lesson not found in this course'
            ], 404);
        }

        // Check if user is enrolled
        if (!auth()->user()->isEnrolledIn($course->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not enrolled in this course'
            ], 403);
        }

        $progress = LessonProgress::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'lesson_id' => $lesson->id
            ],
            [
                'is_completed' => true,
                'completed_at' => now(),
                'watch_time' => $request->get('watch_time', 0)
            ]
        );

        // Update course progress
        $enrollment = auth()->user()->enrollments()->where('course_id', $course->id)->first();
        if ($enrollment) {
            $enrollment->updateProgress();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Lesson marked as complete',
            'data' => $progress
        ]);
    }

    public function updateProgress(Request $request, Course $course, Lesson $lesson)
    {
        // Check if lesson belongs to course
        if ($lesson->course_id !== $course->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lesson not found in this course'
            ], 404);
        }

        // Check if user is enrolled
        if (!auth()->user()->isEnrolledIn($course->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not enrolled in this course'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'watch_time' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $progress = LessonProgress::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'lesson_id' => $lesson->id
            ],
            [
                'watch_time' => $request->watch_time
            ]
        );

        return response()->json([
            'status' => 'success',
            'data' => $progress
        ]);
    }

    /**
     * Store a lesson in a section (instructor method)
     */
    public function storeInSection(Request $request, $sectionId)
    {
        $section = Section::with('course')->findOrFail($sectionId);
        
        // Check if user is the instructor of this course
        if ($section->course->instructor_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized. You are not the instructor of this course.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:video,text,quiz,file',
            'video_url' => 'nullable|url',
            'content' => 'nullable|string',
            'attachments' => 'nullable|array',
            'is_preview' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'video_duration' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // If no sort_order provided, set it to be last in the section
        $sortOrder = $request->sort_order ?? ($section->lessons()->max('sort_order') + 1);

        $lesson = Lesson::create([
            'section_id' => $sectionId,
            'course_id' => $section->course_id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'video_url' => $request->video_url,
            'content' => $request->content,
            'attachments' => $request->attachments,
            'is_preview' => $request->is_preview ?? false,
            'sort_order' => $sortOrder,
            'video_duration' => $request->video_duration ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lesson created successfully',
            'data' => $lesson
        ], 201);
    }

    /**
     * Update a lesson (instructor method)
     */
    public function updateLesson(Request $request, $id)
    {
        $lesson = Lesson::with(['section.course'])->findOrFail($id);
        
        // Check if user is the instructor of this course
        if ($lesson->section->course->instructor_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized. You are not the instructor of this course.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|required|in:video,text,quiz,file',
            'video_url' => 'nullable|url',
            'content' => 'nullable|string',
            'attachments' => 'nullable|array',
            'is_preview' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'video_duration' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $lesson->update($request->only([
            'title', 'description', 'type', 'video_url', 'content', 
            'attachments', 'is_preview', 'sort_order', 'video_duration'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Lesson updated successfully',
            'data' => $lesson->fresh()
        ]);
    }

    /**
     * Delete a lesson (instructor method)
     */
    public function deleteLesson($id)
    {
        $lesson = Lesson::with(['section.course'])->findOrFail($id);
        
        // Check if user is the instructor of this course
        if ($lesson->section->course->instructor_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized. You are not the instructor of this course.'], 403);
        }

        $lesson->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lesson deleted successfully'
        ]);
    }

    /**
     * Reorder lessons in a section
     */
    public function reorderLessons(Request $request, $sectionId)
    {
        $section = Section::with('course')->findOrFail($sectionId);
        
        // Check if user is the instructor of this course
        if ($section->course->instructor_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized. You are not the instructor of this course.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'lessons' => 'required|array',
            'lessons.*.id' => 'required|exists:lessons,id',
            'lessons.*.sort_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->lessons as $lessonData) {
            Lesson::where('id', $lessonData['id'])
                ->where('section_id', $sectionId)
                ->update(['sort_order' => $lessonData['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lessons reordered successfully'
        ]);
    }

    /**
     * Upload lesson video
     */
    public function uploadVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video' => 'required|file|mimes:mp4,avi,mov,wmv|max:102400', // 100MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('video')) {
            $path = $request->file('video')->store('lesson-videos', 'public');
            
            return response()->json([
                'success' => true,
                'message' => 'Video uploaded successfully',
                'data' => [
                    'video_url' => Storage::url($path),
                    'path' => $path
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No video file found'
        ], 400);
    }

    private function hasAccess($course)
    {
        return auth()->check() && (
            auth()->user()->isEnrolledIn($course->id) ||
            $course->instructor_id === auth()->id() ||
            auth()->user()->hasRole('admin')
        );
    }
}