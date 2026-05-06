<?php
/**
 * ============================================================================
 * FILE: src/Helpers/UrlHelper.php
 * URL Building and manipulation utilities
 * ============================================================================
 */

declare(strict_types=1);

namespace SalesPro\SDK\Helpers;
use SalesPro\SDK\Config\Configuration;

class UrlHelper
{
    /**
     * Build query string from parameters array
     *
     * @param array $params Query parameters
     * @return string URL-encoded query string
     */
    public static function buildQueryString(array $params = []): string
    {
        if (empty($params)) return '';
        
        $parts = [];
        
        foreach ($params as $key => $value) {
            if ($value === null || $value === '' || $value === []) continue;
            
            if (is_array($value)) {
                foreach ($value as $val) {
                    $parts[] = urlencode($key) . '[]=' . urlencode((string)$val);
                }
            } else {
                $parts[] = urlencode((string)$key) . '=' . urlencode((string)$value);
            }
        }
        
        return implode('&', $parts);
    }
    
    /**
     * Parse URL into components
     *
     * @param string $url Full URL
     * @return array Parsed components [scheme, host, path, query, fragment]
     */
    public static function parseUrl(string $url): array
    {
        $parsed = parse_url($url);
        
        return [
            'scheme' => $parsed['scheme'] ?? 'https',
            'host' => $parsed['host'] ?? '',
            'path' => $parsed['path'] ?? '/',
            'query' => $parsed['query'] ?? '',
            'fragment' => $parsed['fragment'] ?? ''
        ];
    }
    
    /**
     * Join base URL with endpoint path
     *
     * @param string $baseUrl Base API URL
     * @param string $endpoint Endpoint path
     * @return string Complete URL
     */
    public static function joinUrl(string $baseUrl, string $endpoint): string
    {
        $baseUrl = rtrim($baseUrl, '/');
        $endpoint = ltrim($endpoint, '/');
        
        // Ensure proper joining
        if (!str_contains($baseUrl, 'http://') && !str_contains($baseUrl, 'https://')) {
            $baseUrl = 'https://' . $baseUrl;
        }
        
        return $baseUrl . '/' . ltrim($endpoint, '/');
    }
    
    /**
     * Add query string to URL
     *
     * @param string $url Base URL
     * @param array $params Query params
     * @return string URL with query appended
     */
    public static function addQuery(string $url, array $params = []): string
    {
        if (empty($params)) return $url;
        
        $queryString = self::buildQueryString($params);
        
        $glue = strpos($url, '?') !== false ? '&' : '?';
        
        return $url . $glue . $queryString;
    }
    
    /**
     * Remove query string from URL
     *
     * @param string $url URL with query string
     * @return string Cleaned URL without query
     */
    public static function removeQuery(string $url): string
    {
        $pos = strpos($url, '?');
        return $pos !== false ? substr($url, 0, $pos) : $url;
    }
    
    /**
     * Get URL path only (without domain)
     *
     * @param string $url Full URL
     * @string Path portion of URL
     */
    public static function getPath(string $url): string
    {
        $parsed = self::parseUrl($url);
        return $parsed['path'] ?? '/';
    }
    
    /**
     * Check if URL is absolute
     *
     * @param string $url URL to check
     * @bool Is absolute URL
     */
    public static function isAbsolute(string $url): bool
    {
        return str_starts_with($url, 'http://') || 
               str_starts_with($url, 'https://');
    }
    
    /**
     * Encode URL component safely
     *
     * @param string $value Value to encode
     * @return string Encoded value
     */
    public static function encode(string $value): string
    {
        return rawurlencode($value);
    }
    
    /**
     * Decode URL-encoded string
     *
     * @param string $encoded Encoded string
     @return string Decoded string
     */
    public static function decode(string $encoded): string
    {
        return urldecode($encoded);
    }
    
    /**
     * Build URL for API request
     *
     * @param Configuration $config Configuration object
     * @param string $endpoint API endpoint
     * @param array $params Optional query parameters
     * @return string Fully qualified URL
     */
    public static function buildApiUrl(Configuration $config, string $endpoint, array $params = []): string
    {
        $url = $config->getApiBaseUrl() . '/' . trim($endpoint, '/');
        
        if (!empty($params)) {
            $url .= '?' . self::buildQueryString($params);
        }
        
        return $url;
    }
}