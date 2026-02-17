<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueryOptimizationService
{
    /**
     * Optimize course queries with eager loading and select optimization
     */
    public static function getOptimizedCourses($filters = [])
    {
        $query = \App\Models\Course::query()
            ->select([
                'id', 'title', 'slug', 'short_description', 'thumbnail',
                'price', 'discounted_price', 'level', 'status', 'is_free',
                'is_featured', 'instructor_id', 'category_id', 'created_at',
                'enrollments_count', 'rating_avg', 'lessons_count'
            ])
            ->with([
                'instructor:id,name,email',
                'category:id,name,slug,color'
            ])
            ->where('status', 'approved');

        // Apply filters with indexed columns
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        if (isset($filters['is_free'])) {
            $query->where('is_free', $filters['is_free']);
        }

        if (isset($filters['is_featured'])) {
            $query->where('is_featured', $filters['is_featured']);
        }

        if (isset($filters['price_min'])) {
            $query->where('price', '>=', $filters['price_min']);
        }

        if (isset($filters['price_max'])) {
            $query->where('price', '<=', $filters['price_max']);
        }

        // Order by indexed columns
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDirection = $filters['order_direction'] ?? 'desc';
        
        $query->orderBy($orderBy, $orderDirection);

        return $query;
    }

    /**
     * Optimize user enrollment queries
     */
    public static function getUserEnrollments($userId, $status = 'active')
    {
        return \App\Models\Enrollment::query()
            ->select([
                'id', 'course_id', 'user_id', 'status', 'enrolled_at',
                'expires_at', 'progress_percentage', 'amount_paid'
            ])
            ->with([
                'course:id,title,slug,thumbnail,price,level,instructor_id,category_id',
                'course.instructor:id,name',
                'course.category:id,name,slug,color'
            ])
            ->where('user_id', $userId)
            ->where('status', $status)
            ->orderBy('enrolled_at', 'desc');
    }

    /**
     * Optimize lesson progress queries
     */
    public static function getLessonProgress($userId, $courseId = null)
    {
        $query = \App\Models\LessonProgress::query()
            ->select([
                'id', 'user_id', 'course_id', 'lesson_id', 'is_completed',
                'completed_at', 'time_spent', 'updated_at'
            ])
            ->with([
                'lesson:id,title,slug,course_id,section_id,sort_order,duration',
                'lesson.section:id,title,course_id,sort_order'
            ])
            ->where('user_id', $userId);

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        return $query->orderBy('updated_at', 'desc');
    }

    /**
     * Optimize forum queries with pagination
     */
    public static function getForumTopics($forumId, $perPage = 15)
    {
        return \App\Models\ForumTopic::query()
            ->select([
                'id', 'forum_id', 'user_id', 'title', 'slug', 'is_pinned',
                'is_locked', 'replies_count', 'views_count', 'created_at',
                'updated_at', 'last_reply_at'
            ])
            ->with([
                'user:id,name,avatar',
                'lastReply:id,topic_id,user_id,created_at',
                'lastReply.user:id,name,avatar'
            ])
            ->where('forum_id', $forumId)
            ->orderBy('is_pinned', 'desc')
            ->orderBy('last_reply_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Optimize quiz queries
     */
    public static function getQuizWithQuestions($quizId)
    {
        return \App\Models\Quiz::query()
            ->select([
                'id', 'course_id', 'lesson_id', 'title', 'description',
                'time_limit', 'passing_score', 'max_attempts', 'is_published',
                'questions_count', 'created_at'
            ])
            ->with([
                'questions:id,quiz_id,question,question_type,options,correct_answer,sort_order,points',
                'course:id,title,slug',
                'lesson:id,title,slug'
            ])
            ->where('id', $quizId)
            ->where('is_published', true)
            ->first();
    }

    /**
     * Optimize analytics queries with aggregation
     */
    public static function getCourseAnalytics($courseId)
    {
        return DB::table('enrollments')
            ->select([
                DB::raw('COUNT(*) as total_enrollments'),
                DB::raw('COUNT(CASE WHEN status = "active" THEN 1 END) as active_enrollments'),
                DB::raw('COUNT(CASE WHEN status = "completed" THEN 1 END) as completed_enrollments'),
                DB::raw('AVG(progress_percentage) as avg_progress'),
                DB::raw('SUM(amount_paid) as total_revenue')
            ])
            ->where('course_id', $courseId)
            ->first();
    }

    /**
     * Optimize user dashboard queries
     */
    public static function getUserDashboardData($userId)
    {
        $enrollments = self::getUserEnrollments($userId)->limit(5)->get();
        
        $recentProgress = self::getLessonProgress($userId)
            ->limit(10)
            ->get();

        $upcomingDeadlines = \App\Models\Enrollment::query()
            ->select(['id', 'course_id', 'expires_at'])
            ->with(['course:id,title,slug'])
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->orderBy('expires_at')
            ->limit(5)
            ->get();

        return [
            'enrollments' => $enrollments,
            'recent_progress' => $recentProgress,
            'upcoming_deadlines' => $upcomingDeadlines
        ];
    }

    /**
     * Optimize search queries with full-text search
     */
    public static function searchCourses($searchTerm, $filters = [])
    {
        $query = \App\Models\Course::query()
            ->select([
                'id', 'title', 'slug', 'short_description', 'thumbnail',
                'price', 'level', 'status', 'is_free', 'is_featured',
                'instructor_id', 'category_id', 'created_at'
            ])
            ->with(['instructor:id,name,avatar', 'category:id,name,slug,color'])
            ->where('status', 'approved');

        // Full-text search on indexed columns
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('short_description', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('tags', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Apply additional filters
        foreach ($filters as $key => $value) {
            if (in_array($key, ['category_id', 'level', 'is_free', 'is_featured'])) {
                $query->where($key, $value);
            }
        }

        return $query->orderBy('is_featured', 'desc')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Optimize notification queries
     */
    public static function getUserNotifications($userId, $unreadOnly = false)
    {
        $query = \App\Models\Notification::query()
            ->select(['id', 'type', 'data', 'read_at', 'created_at'])
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', 'App\\Models\\User');

        if ($unreadOnly) {
            $query->whereNull('read_at');
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Batch update operations for better performance
     */
    public static function batchUpdateProgress($progressData)
    {
        try {
            DB::transaction(function () use ($progressData) {
                foreach (array_chunk($progressData, 1000) as $chunk) {
                    \App\Models\LessonProgress::upsert(
                        $chunk,
                        ['user_id', 'lesson_id'],
                        ['is_completed', 'completed_at', 'time_spent', 'updated_at']
                    );
                }
            });
            return true;
        } catch (\Exception $e) {
            Log::error('Batch update failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get query execution plan for debugging
     */
    public static function explainQuery(Builder $query)
    {
        $sql = $query->toSql();
        $bindings = $query->getBindings();
        
        $explainQuery = "EXPLAIN " . $sql;
        
        return DB::select($explainQuery, $bindings);
    }

    /**
     * Optimize database connection settings
     */
    public static function optimizeDatabaseSettings()
    {
        $settings = [
            'innodb_buffer_pool_size' => '1G',
            'innodb_log_file_size' => '256M',
            'innodb_flush_log_at_trx_commit' => '2',
            'innodb_flush_method' => 'O_DIRECT',
            'query_cache_size' => '64M',
            'query_cache_type' => '1',
            'tmp_table_size' => '64M',
            'max_heap_table_size' => '64M',
            'key_buffer_size' => '256M',
            'sort_buffer_size' => '2M',
            'read_buffer_size' => '2M',
            'read_rnd_buffer_size' => '8M',
            'myisam_sort_buffer_size' => '64M',
            'thread_cache_size' => '8',
            'table_open_cache' => '4000',
            'max_connections' => '200'
        ];

        return $settings;
    }
}
