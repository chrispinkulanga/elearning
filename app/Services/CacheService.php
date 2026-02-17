<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class CacheService
{
    /**
     * Cache TTL constants (in seconds)
     */
    const TTL_SHORT = 300;      // 5 minutes
    const TTL_MEDIUM = 3600;    // 1 hour
    const TTL_LONG = 86400;     // 24 hours
    const TTL_VERY_LONG = 604800; // 7 days

    /**
     * Cache key prefixes
     */
    const PREFIX_USER = 'user:';
    const PREFIX_COURSE = 'course:';
    const PREFIX_CATEGORY = 'category:';
    const PREFIX_LESSON = 'lesson:';
    const PREFIX_QUIZ = 'quiz:';
    const PREFIX_FORUM = 'forum:';
    const PREFIX_ANALYTICS = 'analytics:';

    /**
     * Cache user data with optimized TTL
     */
    public static function cacheUser($userId, $userData, $ttl = self::TTL_MEDIUM)
    {
        $key = self::PREFIX_USER . $userId;
        return Cache::put($key, $userData, $ttl);
    }

    /**
     * Get cached user data
     */
    public static function getCachedUser($userId)
    {
        $key = self::PREFIX_USER . $userId;
        return Cache::get($key);
    }

    /**
     * Cache course data with relationships
     */
    public static function cacheCourse($courseId, $courseData, $ttl = self::TTL_LONG)
    {
        $key = self::PREFIX_COURSE . $courseId;
        return Cache::put($key, $courseData, $ttl);
    }

    /**
     * Get cached course data
     */
    public static function getCachedCourse($courseId)
    {
        $key = self::PREFIX_COURSE . $courseId;
        return Cache::get($key);
    }

    /**
     * Cache categories with hierarchical structure
     */
    public static function cacheCategories($categories, $ttl = self::TTL_VERY_LONG)
    {
        $key = self::PREFIX_CATEGORY . 'all';
        return Cache::put($key, $categories, $ttl);
    }

    /**
     * Get cached categories
     */
    public static function getCachedCategories()
    {
        $key = self::PREFIX_CATEGORY . 'all';
        return Cache::get($key);
    }

    /**
     * Cache lesson content
     */
    public static function cacheLesson($lessonId, $lessonData, $ttl = self::TTL_LONG)
    {
        $key = self::PREFIX_LESSON . $lessonId;
        return Cache::put($key, $lessonData, $ttl);
    }

    /**
     * Get cached lesson
     */
    public static function getCachedLesson($lessonId)
    {
        $key = self::PREFIX_LESSON . $lessonId;
        return Cache::get($key);
    }

    /**
     * Cache quiz data
     */
    public static function cacheQuiz($quizId, $quizData, $ttl = self::TTL_MEDIUM)
    {
        $key = self::PREFIX_QUIZ . $quizId;
        return Cache::put($key, $quizData, $ttl);
    }

    /**
     * Get cached quiz
     */
    public static function getCachedQuiz($quizId)
    {
        $key = self::PREFIX_QUIZ . $quizId;
        return Cache::get($key);
    }

    /**
     * Cache forum posts
     */
    public static function cacheForumPosts($forumId, $posts, $ttl = self::TTL_MEDIUM)
    {
        $key = self::PREFIX_FORUM . 'posts:' . $forumId;
        return Cache::put($key, $posts, $ttl);
    }

    /**
     * Get cached forum posts
     */
    public static function getCachedForumPosts($forumId)
    {
        $key = self::PREFIX_FORUM . 'posts:' . $forumId;
        return Cache::get($key);
    }

    /**
     * Cache analytics data
     */
    public static function cacheAnalytics($type, $data, $ttl = self::TTL_SHORT)
    {
        $key = self::PREFIX_ANALYTICS . $type;
        return Cache::put($key, $data, $ttl);
    }

    /**
     * Get cached analytics
     */
    public static function getCachedAnalytics($type)
    {
        $key = self::PREFIX_ANALYTICS . $type;
        return Cache::get($key);
    }

    /**
     * Cache with tags for better invalidation
     */
    public static function cacheWithTags($key, $data, $tags, $ttl = self::TTL_MEDIUM)
    {
        return Cache::tags($tags)->put($key, $data, $ttl);
    }

    /**
     * Invalidate cache by tags
     */
    public static function invalidateByTags($tags)
    {
        return Cache::tags($tags)->flush();
    }

    /**
     * Remember pattern with fallback
     */
    public static function remember($key, $ttl, $callback)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Remember forever with manual invalidation
     */
    public static function rememberForever($key, $callback)
    {
        return Cache::rememberForever($key, $callback);
    }

    /**
     * Cache paginated results
     */
    public static function cachePaginated($key, $page, $data, $ttl = self::TTL_MEDIUM)
    {
        $paginatedKey = $key . ':page:' . $page;
        return Cache::put($paginatedKey, $data, $ttl);
    }

    /**
     * Get cached paginated results
     */
    public static function getCachedPaginated($key, $page)
    {
        $paginatedKey = $key . ':page:' . $page;
        return Cache::get($paginatedKey);
    }

    /**
     * Warm up cache with frequently accessed data
     */
    public static function warmUpCache()
    {
        try {
            // Warm up categories
            if (!self::getCachedCategories()) {
                $categories = \App\Models\Category::all();
                self::cacheCategories($categories);
            }

            // Warm up popular courses
            $popularCourses = \App\Models\Course::with(['instructor', 'category'])
                ->where('status', 'approved')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();
            
            foreach ($popularCourses as $course) {
                self::cacheCourse($course->id, $course);
            }

            Log::info('Cache warmed up successfully');
            return true;
        } catch (\Exception $e) {
            Log::error('Cache warm up failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all application cache
     */
    public static function clearAllCache()
    {
        try {
            Cache::flush();
            Log::info('All cache cleared successfully');
            return true;
        } catch (\Exception $e) {
            Log::error('Cache clear failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cache statistics
     */
    public static function getCacheStats()
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = Redis::connection();
                $info = $redis->info();
                
                return [
                    'driver' => 'redis',
                    'used_memory' => $info['used_memory_human'] ?? 'N/A',
                    'connected_clients' => $info['connected_clients'] ?? 'N/A',
                    'total_commands_processed' => $info['total_commands_processed'] ?? 'N/A',
                    'keyspace_hits' => $info['keyspace_hits'] ?? 'N/A',
                    'keyspace_misses' => $info['keyspace_misses'] ?? 'N/A',
                ];
            }
            
            return [
                'driver' => config('cache.default'),
                'status' => 'active'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get cache stats: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
