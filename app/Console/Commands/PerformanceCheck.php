<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PerformanceMonitoringService;
use App\Services\CacheService;

class PerformanceCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'performance:check {--report : Generate detailed performance report}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check system performance and optimization status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Laravel E-learning Backend Performance Check');
        $this->line('================================================');

        // Get performance metrics
        $metrics = PerformanceMonitoringService::getPerformanceMetrics();

        // Display optimization status
        $this->displayOptimizationStatus($metrics['optimization_status']);

        // Display PHP metrics
        $this->displayPhpMetrics($metrics['php']);

        // Display Opcache metrics
        $this->displayOpcacheMetrics($metrics['opcache']);

        // Display cache metrics
        $this->displayCacheMetrics($metrics['cache']);

        // Display database metrics
        $this->displayDatabaseMetrics($metrics['database']);

        // Display memory metrics
        $this->displayMemoryMetrics($metrics['memory']);

        // Run benchmark if requested
        if ($this->option('report')) {
            $this->runBenchmark();
        }

        $this->line('================================================');
        $this->info('✅ Performance check completed!');
    }

    /**
     * Display optimization status
     */
    private function displayOptimizationStatus($status)
    {
        $this->line('');
        $this->info('📊 Optimization Status:');
        
        $level = $status['optimization_level'];
        $color = $this->getOptimizationColor($level['percentage']);
        
        $this->line("   Level: <fg={$color}>{$level['level']}</> ({$level['percentage']}%)");
        $this->line("   Score: {$level['score']}/{$level['max_score']}");
        
        $this->line('   Features:');
        $this->line('   • Opcache: ' . ($status['opcache_enabled'] ? '✅ Enabled' : '❌ Disabled'));
        $this->line('   • Octane: ' . ($status['octane_available'] ? '✅ Available' : '❌ Not Available'));
        $this->line('   • Redis: ' . ($status['redis_available'] ? '✅ Available' : '❌ Not Available'));
        $this->line('   • Cache Driver: ' . $status['cache_driver']);
        $this->line('   • Session Driver: ' . $status['session_driver']);
        $this->line('   • Queue Driver: ' . $status['queue_driver']);
    }

    /**
     * Display PHP metrics
     */
    private function displayPhpMetrics($php)
    {
        $this->line('');
        $this->info('🐘 PHP Configuration:');
        $this->line('   Version: ' . $php['version']);
        $this->line('   Memory Limit: ' . $php['memory_limit']);
        $this->line('   Memory Usage: ' . $this->formatBytes($php['memory_usage']));
        $this->line('   Peak Memory: ' . $this->formatBytes($php['memory_peak']));
        $this->line('   Execution Time: ' . round($php['execution_time'], 4) . 's');
        $this->line('   Max Execution Time: ' . $php['max_execution_time'] . 's');
    }

    /**
     * Display Opcache metrics
     */
    private function displayOpcacheMetrics($opcache)
    {
        $this->line('');
        $this->info('⚡ Opcache Status:');
        
        if (!$opcache['enabled']) {
            $this->line('   ❌ Opcache is disabled');
            return;
        }

        $this->line('   ✅ Opcache is enabled');
        $this->line('   Hit Rate: ' . $opcache['hit_rate'] . '%');
        $this->line('   Cached Scripts: ' . $opcache['cached_scripts']);
        $this->line('   Memory Usage: ' . $this->formatBytes($opcache['memory_usage']['used_memory']));
        $this->line('   Memory Consumption: ' . $this->formatBytes($opcache['memory_consumption'] * 1024 * 1024));
        $this->line('   JIT: ' . ($opcache['jit_enabled'] ? '✅ Enabled' : '❌ Disabled'));
    }

    /**
     * Display cache metrics
     */
    private function displayCacheMetrics($cache)
    {
        $this->line('');
        $this->info('💾 Cache Status:');
        $this->line('   Driver: ' . $cache['driver']);
        
        if ($cache['driver'] === 'redis') {
            $this->line('   Status: ' . $cache['status']);
            $this->line('   Memory Used: ' . $cache['used_memory']);
            $this->line('   Connected Clients: ' . $cache['connected_clients']);
            $this->line('   Hit Rate: ' . $cache['hit_rate'] . '%');
            $this->line('   Keys Count: ' . $cache['keys_count']);
        } else {
            $this->line('   Status: ' . $cache['status']);
        }
    }

    /**
     * Display database metrics
     */
    private function displayDatabaseMetrics($database)
    {
        $this->line('');
        $this->info('🗄️ Database Status:');
        $this->line('   Driver: ' . $database['driver']);
        $this->line('   Status: ' . $database['status']);
        
        if (isset($database['version'])) {
            $this->line('   Version: ' . $database['version']);
            $this->line('   Uptime: ' . $this->formatUptime($database['uptime']));
            $this->line('   Queries/sec: ' . $database['queries_per_second']);
            $this->line('   Connections: ' . $database['connections'] . '/' . $database['max_connections']);
            $this->line('   Slow Queries: ' . $database['slow_queries']);
        }
    }

    /**
     * Display memory metrics
     */
    private function displayMemoryMetrics($memory)
    {
        $this->line('');
        $this->info('🧠 Memory Usage:');
        $this->line('   Current: ' . $this->formatBytes($memory['current_usage']));
        $this->line('   Peak: ' . $this->formatBytes($memory['peak_usage']));
        $this->line('   Limit: ' . $memory['limit']);
        $this->line('   Usage: ' . $memory['usage_percentage'] . '%');
    }

    /**
     * Run performance benchmark
     */
    private function runBenchmark()
    {
        $this->line('');
        $this->info('🏃 Running Performance Benchmark...');
        
        $benchmark = PerformanceMonitoringService::runBenchmark();
        
        $this->line('   Total Time: ' . $benchmark['total_time'] . 's');
        $this->line('   Memory Used: ' . $this->formatBytes($benchmark['memory_used']));
        $this->line('   Database Query: ' . $benchmark['database_query_time'] . 's');
        $this->line('   Cache Operation: ' . $benchmark['cache_operation_time'] . 's');
        $this->line('   Courses Loaded: ' . $benchmark['courses_loaded']);
    }

    /**
     * Get optimization color based on percentage
     */
    private function getOptimizationColor($percentage)
    {
        if ($percentage >= 90) return 'green';
        if ($percentage >= 75) return 'yellow';
        if ($percentage >= 60) return 'blue';
        return 'red';
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Format uptime to human readable format
     */
    private function formatUptime($seconds)
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        return "{$days}d {$hours}h {$minutes}m";
    }
}
