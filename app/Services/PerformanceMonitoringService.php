<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class PerformanceMonitoringService
{
    /**
     * Get comprehensive performance metrics
     */
    public static function getPerformanceMetrics()
    {
        return [
            'php' => self::getPhpMetrics(),
            'opcache' => self::getOpcacheMetrics(),
            'cache' => self::getCacheMetrics(),
            'database' => self::getDatabaseMetrics(),
            'memory' => self::getMemoryMetrics(),
            'requests' => self::getRequestMetrics(),
            'optimization_status' => self::getOptimizationStatus()
        ];
    }

    /**
     * Get PHP performance metrics
     */
    public static function getPhpMetrics()
    {
        return [
            'version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
            'max_execution_time' => ini_get('max_execution_time'),
            'opcache_enabled' => function_exists('opcache_get_status') && opcache_get_status() !== false
        ];
    }

    /**
     * Get Opcache metrics
     */
    public static function getOpcacheMetrics()
    {
        if (!function_exists('opcache_get_status')) {
            return ['enabled' => false];
        }

        $status = opcache_get_status();
        $config = opcache_get_configuration();

        return [
            'enabled' => $status !== false,
            'memory_usage' => $status['memory_usage'] ?? null,
            'hit_rate' => $status['opcache_statistics']['opcache_hit_rate'] ?? 0,
            'cached_scripts' => $status['opcache_statistics']['num_cached_scripts'] ?? 0,
            'cached_keys' => $status['opcache_statistics']['num_cached_keys'] ?? 0,
            'max_cached_scripts' => $config['directives']['opcache.max_accelerated_files'] ?? 0,
            'memory_consumption' => $config['directives']['opcache.memory_consumption'] ?? 0,
            'jit_enabled' => $config['directives']['opcache.jit'] ?? false
        ];
    }

    /**
     * Get cache performance metrics
     */
    public static function getCacheMetrics()
    {
        $driver = config('cache.default');
        $metrics = ['driver' => $driver];

        try {
            if ($driver === 'redis') {
                $redis = Redis::connection();
                $info = $redis->info();
                
                $metrics = array_merge($metrics, [
                    'status' => 'connected',
                    'used_memory' => $info['used_memory_human'] ?? 'N/A',
                    'connected_clients' => $info['connected_clients'] ?? 0,
                    'total_commands' => $info['total_commands_processed'] ?? 0,
                    'keyspace_hits' => $info['keyspace_hits'] ?? 0,
                    'keyspace_misses' => $info['keyspace_misses'] ?? 0,
                    'hit_rate' => self::calculateHitRate($info),
                    'keys_count' => $redis->dbsize()
                ]);
            } else {
                $metrics['status'] = 'active';
            }
        } catch (\Exception $e) {
            $metrics['status'] = 'error';
            $metrics['error'] = $e->getMessage();
        }

        return $metrics;
    }

    /**
     * Get database performance metrics
     */
    public static function getDatabaseMetrics()
    {
        try {
            $connection = DB::connection();
            $pdo = $connection->getPdo();
            
            // Get basic connection info
            $metrics = [
                'driver' => $connection->getDriverName(),
                'status' => 'connected',
                'host' => $connection->getConfig('host'),
                'database' => $connection->getConfig('database')
            ];

            // Get MySQL-specific metrics if available
            if ($connection->getDriverName() === 'mysql') {
                $mysqlMetrics = self::getMysqlMetrics($connection);
                $metrics = array_merge($metrics, $mysqlMetrics);
            }

            return $metrics;
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get MySQL-specific performance metrics
     */
    private static function getMysqlMetrics($connection)
    {
        try {
            $status = $connection->select('SHOW STATUS');
            $variables = $connection->select('SHOW VARIABLES');
            
            $statusArray = [];
            foreach ($status as $row) {
                $statusArray[$row->Variable_name] = $row->Value;
            }
            
            $variablesArray = [];
            foreach ($variables as $row) {
                $variablesArray[$row->Variable_name] = $row->Value;
            }

            return [
                'version' => $statusArray['Version'] ?? 'Unknown',
                'uptime' => $statusArray['Uptime'] ?? 0,
                'queries_per_second' => round(($statusArray['Queries'] ?? 0) / max(($statusArray['Uptime'] ?? 1), 1), 2),
                'connections' => $statusArray['Threads_connected'] ?? 0,
                'max_connections' => $variablesArray['max_connections'] ?? 0,
                'innodb_buffer_pool_size' => $variablesArray['innodb_buffer_pool_size'] ?? 0,
                'query_cache_size' => $variablesArray['query_cache_size'] ?? 0,
                'slow_queries' => $statusArray['Slow_queries'] ?? 0,
                'table_locks_waited' => $statusArray['Table_locks_waited'] ?? 0
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get memory usage metrics
     */
    public static function getMemoryMetrics()
    {
        return [
            'current_usage' => memory_get_usage(true),
            'peak_usage' => memory_get_peak_usage(true),
            'limit' => ini_get('memory_limit'),
            'usage_percentage' => round((memory_get_usage(true) / self::convertToBytes(ini_get('memory_limit'))) * 100, 2)
        ];
    }

    /**
     * Get request performance metrics
     */
    public static function getRequestMetrics()
    {
        return [
            'request_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
            'memory_peak' => memory_get_peak_usage(true),
            'included_files' => count(get_included_files()),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown'
        ];
    }

    /**
     * Get optimization status
     */
    public static function getOptimizationStatus()
    {
        return [
            'opcache_enabled' => function_exists('opcache_get_status') && opcache_get_status() !== false,
            'octane_available' => class_exists('Laravel\Octane\Octane'),
            'redis_available' => class_exists('Redis'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
            'optimization_level' => self::getOptimizationLevel()
        ];
    }

    /**
     * Calculate cache hit rate
     */
    private static function calculateHitRate($info)
    {
        $hits = $info['keyspace_hits'] ?? 0;
        $misses = $info['keyspace_misses'] ?? 0;
        $total = $hits + $misses;
        
        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }

    /**
     * Convert memory limit to bytes
     */
    private static function convertToBytes($memoryLimit)
    {
        $memoryLimit = trim($memoryLimit);
        $last = strtolower($memoryLimit[strlen($memoryLimit) - 1]);
        $memoryLimit = (int) $memoryLimit;

        switch ($last) {
            case 'g':
                $memoryLimit *= 1024;
            case 'm':
                $memoryLimit *= 1024;
            case 'k':
                $memoryLimit *= 1024;
        }

        return $memoryLimit;
    }

    /**
     * Get optimization level score
     */
    private static function getOptimizationLevel()
    {
        $score = 0;
        $maxScore = 10;

        // Opcache enabled
        if (function_exists('opcache_get_status') && opcache_get_status() !== false) {
            $score += 2;
        }

        // Redis cache
        if (config('cache.default') === 'redis') {
            $score += 2;
        }

        // Redis session
        if (config('session.driver') === 'redis') {
            $score += 1;
        }

        // Redis queue
        if (config('queue.default') === 'redis') {
            $score += 1;
        }

        // Octane available
        if (class_exists('Laravel\Octane\Octane')) {
            $score += 2;
        }

        // Database indexes (check if migration exists)
        if (file_exists(database_path('migrations/2025_01_15_000003_add_performance_indexes.php'))) {
            $score += 2;
        }

        return [
            'score' => $score,
            'max_score' => $maxScore,
            'percentage' => round(($score / $maxScore) * 100, 2),
            'level' => self::getOptimizationLevelName($score, $maxScore)
        ];
    }

    /**
     * Get optimization level name
     */
    private static function getOptimizationLevelName($score, $maxScore)
    {
        $percentage = ($score / $maxScore) * 100;

        if ($percentage >= 90) return 'Excellent';
        if ($percentage >= 75) return 'Very Good';
        if ($percentage >= 60) return 'Good';
        if ($percentage >= 40) return 'Fair';
        if ($percentage >= 20) return 'Poor';
        return 'Very Poor';
    }

    /**
     * Run performance benchmark
     */
    public static function runBenchmark()
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        // Test database query performance
        $dbStart = microtime(true);
        $courses = \App\Models\Course::with(['instructor', 'category'])->limit(10)->get();
        $dbTime = microtime(true) - $dbStart;

        // Test cache performance
        $cacheStart = microtime(true);
        Cache::put('benchmark_test', 'test_data', 60);
        $cached = Cache::get('benchmark_test');
        $cacheTime = microtime(true) - $cacheStart;

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);

        return [
            'total_time' => round($endTime - $startTime, 4),
            'memory_used' => $endMemory - $startMemory,
            'database_query_time' => round($dbTime, 4),
            'cache_operation_time' => round($cacheTime, 4),
            'courses_loaded' => $courses->count(),
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Generate performance report
     */
    public static function generateReport()
    {
        $metrics = self::getPerformanceMetrics();
        $benchmark = self::runBenchmark();

        return [
            'report_generated_at' => now()->toISOString(),
            'metrics' => $metrics,
            'benchmark' => $benchmark,
            'recommendations' => self::getRecommendations($metrics)
        ];
    }

    /**
     * Get performance recommendations
     */
    private static function getRecommendations($metrics)
    {
        $recommendations = [];

        // Opcache recommendations
        if (!$metrics['opcache']['enabled']) {
            $recommendations[] = 'Enable PHP Opcache for 2-5x faster execution';
        }

        // Cache recommendations
        if ($metrics['cache']['driver'] !== 'redis') {
            $recommendations[] = 'Switch to Redis cache for better performance';
        }

        // Memory recommendations
        $memoryUsage = $metrics['memory']['usage_percentage'];
        if ($memoryUsage > 80) {
            $recommendations[] = 'Consider increasing PHP memory limit (currently at ' . $memoryUsage . '%)';
        }

        // Database recommendations
        if (isset($metrics['database']['slow_queries']) && $metrics['database']['slow_queries'] > 0) {
            $recommendations[] = 'Optimize slow database queries (' . $metrics['database']['slow_queries'] . ' detected)';
        }

        return $recommendations;
    }
}
