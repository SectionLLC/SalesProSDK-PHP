<?php
/**
 * ============================================================================
 * FILE: src/Traits/CacheableTrait.php
 * Caching Logic for GET requests with TTL support
 * Supports: File cache, Redis, Memcached, Database, Framework cache
 * ============================================================================
 */

declare(strict_types=1);

namespace SalesProSDK\Traits;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use SalesPro\SalesPro;

trait CacheableTrait
{
    /**
     * Get cached data or execute callback and store result
     *
     * @param string $key Unique cache key
     * @param callable $callback Function to execute if not cached
     * @param int|null $ttl Time-to-live in seconds
     * @return mixed Cached or fresh data
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $client = $this->getClient();
        
        // Check if caching is enabled
        if (!$client->config->get('cache.enabled')) {
            return $callback();
        }
        
        $prefix = $client->config->get('cache.prefix', 'salespro:');
        $fullKey = $prefix . md5($key);
        
        $ttl = $ttl ?? (int)($client->config->get('cache.ttl', 3600));
        
        try {
            // Try framework's cache first
            if (function_exists('cache') && class_exists(\Illuminate\Support\Facades\Cache::class)) {
                $cached = \Illuminate\Support\Facades\Cache::get($fullKey);
                
                if ($cached !== null) {
                    $this->metrics['cache_hits']++;
                    $this->log('debug', "Cache HIT: {$fullKey}");
                    return unserialize($cached);
                }
                
                // Store result after generation
                $result = $callback();
                \Illuminate\Support\Facades\Cache::put(
                    $fullKey,
                    serialize($result),
                    now()->addSeconds($ttl)
                );
                
                return $result;
            }
            
            // Fallback to array-based caching
            $cache = [];
            
            if (isset($_SESSION['__salespro_cache'])) {
                $cache = $_SESSION['__salespro_cache'];
            } else {
                $_SESSION['__salespro_cache'] = $cache = [];
            }
            
            if (isset($cache[$fullKey]) && !$cache[$fullKey]['expired']) {
                $this->metrics['cache_hits']++;
                $this->log('debug', "Session Cache HIT: {$fullKey}");
                return $cache[$fullKey]['data'];
            }
            
            // Execute and store
            $result = $callback();
            $cache[$fullKey] = [
                'data' => $result,
                'created_at' => time(),
                'expires_at' => time() + $ttl,
                'expired' => false
            ];
            
            $_SESSION['__salespro_cache'] = $cache;
            
            return $result;
            
        } catch (\Throwable $e) {
            // If caching fails, just execute callback
            return $callback();
        }
    }
    
    /**
     * Clear specific cache entry
     */
    public function forget(string $key): bool
    {
        $prefix = $this->getClient()->config->get('cache.prefix', 'salespro:');
        $fullKey = $prefix . md5($key);
        
        try {
            if (function_exists('cache') && class_exists(\Illuminate\Support\Facades\Cache::class)) {
                return \Illuminate\Support\Facades\Cache::forget($fullKey);
            }
            
            if (isset($_SESSION['__salespro_cache'][$fullKey])) {
                unset($_SESSION['__salespro_cache'][$fullKey]);
                return true;
            }
        } catch (\Throwable $e) {
            return false;
        }
    }
    
    /**
     * Clear all SalesPro cache entries
     */
    public function clearCache(): int
    {
        $count = 0;
        $prefix = $this->getClient()->config->get('prefix', 'salespro:');
        
        try {
            if (event('cache')) {
                $tags = collect(Cache::tags());
                foreach ($tags as $tag) {
                    if (str_starts_with($tag, $prefix)) {
                        Cache::tags(array_filter($tags, fn($t) => str_starts_with($tag, $prefix)));
                    }
                }
                $count += Cache::flush();
            }
            
            if (isset($_SESSION)) {
                if (isset($_SESSION['__salespro_cache'])) {
                    $count += count($_SESSION['__salespro_cache']);
                    unset($_SESSION['__salespro_cache']);
                }
            }
        } catch (\Throwable $e) {
            // Silently fail - caching is optional
        }
        
        $this->log('info', "Cleared {$count} cache entries");
        return $count;
    }
    
    /**
     * Check if key exists in cache
     */
    public function has(string $key): bool
    {
        $prefix = $this->getClient()->config->get('prefix', 'salespro:');
        $fullKey = $prefix . md5($key);
        
        try {
            if (event('cache')) {
                return \Illuminate\Support\Facades\Cache::has($fullKey);
            }
            
            return isset($_SESSION['__salespro_cache'][$fullKey]) && !$_SESSION['__salespro_cache'][$fullKey]['expired'];
        } catch (\Throwable $e) {
            return false;
        }
    }
    
    /**
     * Generate cache key from endpoint + params
     */
    protected function getCacheKey(string $endpoint, array $params = []): string
    {
        $key = $endpoint . '?' . http_build_query($params);
        return md5($key);
    }
    
    /**
     * Set cache TTL for next operation
     */
    public function setTTL(int $seconds): self
    {
        $this->getClient()->config->set('cache.ttl', $seconds);
        return $this;
    }
}