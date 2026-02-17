<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class CacheOptimization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip caching for authenticated users on sensitive routes
        if ($this->shouldSkipCaching($request)) {
            return $next($request);
        }

        // Generate cache key based on request
        $cacheKey = $this->generateCacheKey($request);

        // Try to get cached response
        $cachedResponse = Cache::get($cacheKey);
        
        if ($cachedResponse !== null) {
            return $this->buildResponseFromCache($cachedResponse);
        }

        // Process the request
        $response = $next($request);

        // Cache the response if it's cacheable
        if ($this->shouldCacheResponse($response)) {
            $this->cacheResponse($cacheKey, $response);
        }

        return $response;
    }

    /**
     * Determine if caching should be skipped for this request
     */
    protected function shouldSkipCaching(Request $request): bool
    {
        // Skip for authenticated users on sensitive routes
        if (auth()->check()) {
            $sensitiveRoutes = [
                'api/user/*',
                'api/courses/*/enroll',
                'api/lessons/*/complete',
                'api/quiz/*/submit',
                'api/forum/*/post',
                'api/payments/*',
            ];

            foreach ($sensitiveRoutes as $route) {
                if ($request->is($route)) {
                    return true;
                }
            }
        }

        // Skip for POST, PUT, DELETE requests
        if (!in_array($request->method(), ['GET', 'HEAD'])) {
            return true;
        }

        // Skip if request has cache-busting parameters
        if ($request->has('_t') || $request->has('nocache')) {
            return true;
        }

        return false;
    }

    /**
     * Generate cache key for the request
     */
    protected function generateCacheKey(Request $request): string
    {
        $key = 'http_cache:' . md5($request->fullUrl());
        
        // Include user ID in key if authenticated
        if (auth()->check()) {
            $key .= ':user:' . auth()->id();
        }

        return $key;
    }

    /**
     * Determine if response should be cached
     */
    protected function shouldCacheResponse(BaseResponse $response): bool
    {
        // Only cache successful responses
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        // Don't cache responses with no-cache headers
        if ($response->headers->has('Cache-Control') && 
            str_contains($response->headers->get('Cache-Control'), 'no-cache')) {
            return false;
        }

        // Don't cache responses with Set-Cookie headers
        if ($response->headers->has('Set-Cookie')) {
            return false;
        }

        return true;
    }

    /**
     * Cache the response
     */
    protected function cacheResponse(string $cacheKey, BaseResponse $response): void
    {
        $ttl = $this->getCacheTTL($response);
        
        $cacheData = [
            'content' => $response->getContent(),
            'headers' => $response->headers->all(),
            'status_code' => $response->getStatusCode(),
        ];

        Cache::put($cacheKey, $cacheData, $ttl);
    }

    /**
     * Build response from cached data
     */
    protected function buildResponseFromCache(array $cachedData): BaseResponse
    {
        $response = Response::make($cachedData['content'], $cachedData['status_code']);

        foreach ($cachedData['headers'] as $name => $values) {
            $response->headers->set($name, $values);
        }

        // Add cache hit header
        $response->headers->set('X-Cache', 'HIT');
        $response->headers->set('X-Cache-Timestamp', now()->toISOString());

        return $response;
    }

    /**
     * Get cache TTL based on response content
     */
    protected function getCacheTTL(BaseResponse $response): int
    {
        // Default TTL
        $ttl = 300; // 5 minutes

        // Longer TTL for static content
        if ($response->headers->has('Content-Type')) {
            $contentType = $response->headers->get('Content-Type');
            
            if (str_contains($contentType, 'text/css') || 
                str_contains($contentType, 'application/javascript')) {
                $ttl = 86400; // 24 hours
            } elseif (str_contains($contentType, 'image/')) {
                $ttl = 604800; // 7 days
            }
        }

        // Check for Cache-Control max-age
        if ($response->headers->has('Cache-Control')) {
            $cacheControl = $response->headers->get('Cache-Control');
            if (preg_match('/max-age=(\d+)/', $cacheControl, $matches)) {
                $ttl = (int) $matches[1];
            }
        }

        return $ttl;
    }
}
