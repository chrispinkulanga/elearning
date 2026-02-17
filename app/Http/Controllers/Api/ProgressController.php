<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CoursePage;
use App\Models\CourseWidget;
use App\Models\UserProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProgressController extends Controller
{
    /**
     * Mark a lesson/widget as complete
     */
    public function markComplete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'course_id' => 'required|exists:courses,id',
                'page_id' => 'nullable|exists:course_pages,id',
                'widget_id' => 'nullable|exists:course_widgets,id',
                'completed' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = auth()->id();
            $courseId = $request->course_id;
            $pageId = $request->page_id;
            $widgetId = $request->widget_id;
            $completed = $request->completed;

            // Check if user is enrolled in the course
            $enrollment = DB::table('enrollments')
                ->where('user_id', $userId)
                ->where('course_id', $courseId)
                ->where('status', 'active')
                ->first();

            if (!$enrollment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not enrolled in this course'
                ], 403);
            }

            // Create or update progress record
            $progress = UserProgress::updateOrCreate(
                [
                    'user_id' => $userId,
                    'course_id' => $courseId,
                    'page_id' => $pageId,
                    'widget_id' => $widgetId
                ],
                [
                    'completed' => $completed,
                    'completed_at' => $completed ? now() : null
                ]
            );

            // Update enrollment progress percentage
            $this->updateEnrollmentProgress($userId, $courseId);

            Log::info('Progress updated', [
                'user_id' => $userId,
                'course_id' => $courseId,
                'page_id' => $pageId,
                'widget_id' => $widgetId,
                'completed' => $completed
            ]);

            return response()->json([
                'status' => 'success',
                'message' => $completed ? 'Marked as complete' : 'Marked as incomplete',
                'data' => $progress
            ]);

        } catch (\Exception $e) {
            Log::error('Progress update error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update progress'
            ], 500);
        }
    }

    /**
     * Get course progress for the authenticated user
     */
    public function getCourseProgress(Course $course)
    {
        try {
            $userId = auth()->id();

            // Check if user is enrolled in the course
            $enrollment = DB::table('enrollments')
                ->where('user_id', $userId)
                ->where('course_id', $course->id)
                ->where('status', 'active')
                ->first();

            if (!$enrollment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not enrolled in this course'
                ], 403);
            }

            // Load course with pages and widgets
            $course->load(['pages.widgets']);

            $progressData = [
                'course_id' => $course->id,
                'course_title' => $course->title,
                'total_pages' => $course->pages->count(),
                'total_widgets' => $course->pages->sum(function($page) {
                    return $page->widgets->count();
                }),
                'pages' => []
            ];

            // Get user progress for this course
            $userProgress = UserProgress::where('user_id', $userId)
                ->where('course_id', $course->id)
                ->get()
                ->keyBy(function($progress) {
                    return $progress->page_id . '_' . $progress->widget_id;
                });

            // Calculate progress for each page
            foreach ($course->pages as $page) {
                $pageProgress = [
                    'page_id' => $page->id,
                    'page_title' => $page->title,
                    'total_widgets' => $page->widgets->count(),
                    'completed_widgets' => 0,
                    'progress_percentage' => 0,
                    'widgets' => []
                ];

                foreach ($page->widgets as $widget) {
                    $progressKey = $page->id . '_' . $widget->id;
                    $isCompleted = $userProgress->get($progressKey)?->completed ?? false;

                    if ($isCompleted) {
                        $pageProgress['completed_widgets']++;
                    }

                    $pageProgress['widgets'][] = [
                        'widget_id' => $widget->id,
                        'widget_type' => $widget->widget_type,
                        'completed' => $isCompleted
                    ];
                }

                // Calculate page progress percentage
                if ($pageProgress['total_widgets'] > 0) {
                    $pageProgress['progress_percentage'] = round(
                        ($pageProgress['completed_widgets'] / $pageProgress['total_widgets']) * 100
                    );
                }

                $progressData['pages'][] = $pageProgress;
            }

            // Calculate overall course progress
            $totalWidgets = $progressData['total_widgets'];
            $totalCompleted = collect($progressData['pages'])->sum('completed_widgets');
            $overallProgress = $totalWidgets > 0 ? round(($totalCompleted / $totalWidgets) * 100) : 0;

            $progressData['overall_progress'] = $overallProgress;
            $progressData['completed_widgets'] = $totalCompleted;

            return response()->json([
                'status' => 'success',
                'data' => $progressData
            ]);

        } catch (\Exception $e) {
            Log::error('Get course progress error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get course progress'
            ], 500);
        }
    }

    /**
     * Update enrollment progress percentage
     */
    private function updateEnrollmentProgress($userId, $courseId)
    {
        try {
            // Get course with pages and widgets
            $course = Course::with(['pages.widgets'])->find($courseId);
            if (!$course) {
                return;
            }

            // Get user progress for this course
            $userProgress = UserProgress::where('user_id', $userId)
                ->where('course_id', $courseId)
                ->get()
                ->keyBy(function($progress) {
                    return $progress->page_id . '_' . $progress->widget_id;
                });

            // Calculate overall course progress
            $totalWidgets = 0;
            $completedWidgets = 0;

            foreach ($course->pages as $page) {
                foreach ($page->widgets as $widget) {
                    $totalWidgets++;
                    $progressKey = $page->id . '_' . $widget->id;
                    $isCompleted = $userProgress->get($progressKey)?->completed ?? false;
                    
                    if ($isCompleted) {
                        $completedWidgets++;
                    }
                }
            }

            // Calculate progress percentage
            $progressPercentage = $totalWidgets > 0 ? round(($completedWidgets / $totalWidgets) * 100) : 0;

            // Update enrollment progress percentage
            DB::table('enrollments')
                ->where('user_id', $userId)
                ->where('course_id', $courseId)
                ->where('status', 'active')
                ->update(['progress_percentage' => $progressPercentage]);

            Log::info('Enrollment progress updated', [
                'user_id' => $userId,
                'course_id' => $courseId,
                'progress_percentage' => $progressPercentage,
                'completed_widgets' => $completedWidgets,
                'total_widgets' => $totalWidgets
            ]);

        } catch (\Exception $e) {
            Log::error('Update enrollment progress error: ' . $e->getMessage());
        }
    }
}
