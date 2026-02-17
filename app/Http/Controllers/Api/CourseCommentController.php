<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseComment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CourseCommentController extends Controller
{
    /**
     * Get comments for a course
     */
    public function index(Request $request, $courseId): JsonResponse
    {
        try {
            $course = Course::findOrFail($courseId);
            
            $comments = CourseComment::with(['user:id,name,email', 'replies.user:id,name,email'])
                ->where('course_id', $courseId)
                ->topLevel()
                ->approved()
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'status' => 'success',
                'data' => $comments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch comments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new comment
     */
    public function store(Request $request, $courseId): JsonResponse
    {
        try {
            $course = Course::findOrFail($courseId);
            
            $request->validate([
                'content' => 'required|string|max:1000',
                'parent_id' => 'nullable|exists:course_comments,id'
            ]);

            $user = Auth::user();
            $isInstructor = $user->hasRole('instructor') && $course->instructor_id === $user->id;

            $comment = CourseComment::create([
                'course_id' => $courseId,
                'user_id' => $user->id,
                'parent_id' => $request->parent_id,
                'content' => $request->content,
                'is_instructor_reply' => $isInstructor,
                'is_approved' => true // Auto-approve for now
            ]);

            $comment->load(['user:id,name,email', 'replies.user:id,name,email']);

            return response()->json([
                'status' => 'success',
                'message' => 'Comment added successfully',
                'data' => $comment
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create comment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a comment
     */
    public function update(Request $request, $courseId, $commentId): JsonResponse
    {
        try {
            $comment = CourseComment::where('course_id', $courseId)
                ->where('id', $commentId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $request->validate([
                'content' => 'required|string|max:1000'
            ]);

            $comment->update([
                'content' => $request->content
            ]);

            $comment->load(['user:id,name,email', 'replies.user:id,name,email']);

            return response()->json([
                'status' => 'success',
                'message' => 'Comment updated successfully',
                'data' => $comment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update comment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a comment
     */
    public function destroy($courseId, $commentId): JsonResponse
    {
        try {
            $comment = CourseComment::where('course_id', $courseId)
                ->where('id', $commentId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $comment->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Comment deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete comment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get comment statistics for a course
     */
    public function stats($courseId): JsonResponse
    {
        try {
            $course = Course::findOrFail($courseId);
            
            $stats = [
                'total_comments' => CourseComment::where('course_id', $courseId)
                    ->approved()
                    ->count(),
                'total_questions' => CourseComment::where('course_id', $courseId)
                    ->topLevel()
                    ->approved()
                    ->count(),
                'total_replies' => CourseComment::where('course_id', $courseId)
                    ->replies()
                    ->approved()
                    ->count(),
                'instructor_replies' => CourseComment::where('course_id', $courseId)
                    ->where('is_instructor_reply', true)
                    ->approved()
                    ->count()
            ];

            return response()->json([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch comment stats: ' . $e->getMessage()
            ], 500);
        }
    }
}