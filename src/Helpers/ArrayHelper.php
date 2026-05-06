<?php
/**
 * ============================================================================
 * FILE: src/Helpers/ArrayHelper.php
 * Array manipulation and data transformation utilities
 * ============================================================================
 */

declare(strict_types=1);

namespace SalesPro\SDK\Helpers;

class ArrayHelper
{
    /**
     * Get value from nested array using dot notation
     *
     * @param array $data Source array
     * @param string $key Dot-separated key path (e.g., 'data.products.0.name')
     * @param mixed $default Default value if not found
     * @return mixed Value at path or default
     */
    public static function get(array $data, string $key, $default = null): mixed
    {
        if (empty($key)) return $default;
        
        $keys = explode('.', $key);
        $current = $data;
        
        foreach ($keys as $i => $part) {
            if (!is_array($current) || !array_key_exists($current, $part)) {
                return $default;
            }
            
            $current = $current[$part] ?? $default;
        }
        
        return $current;
    }
    
    /**
     * Set value in nested array using dot notation
     *
     * @param array &$data Target array (passed by reference)
     * @param string $key Dot-separated key path
     * @param mixed $value Value to set
     * @return void
     */
    public static function set(array &$data, string $key, $value): void
    {
        if (empty($key)) return;
        
        $keys = explode('.', $key);
        $current = &$data;
        
        foreach ($keys as $i => $part) {
            $lastKey = array_pop($keys);
            
            if (!is_array($current)) {
                $current = [];
            }
            
            if ($i === count($keys)) {
                $current[$part] = $value;
                return; // Done!
            }
            
            $current = &$current[$part];
        }
    }
    
    /**
     * Check if array is associative or indexed
     */
    public static function isAssociative(array $arr): bool
    {
        return array_keys($arr) !== range(0, count($arr));
    }
    
    /**
     * Flatten multi-dimensional array to single level
     */
    public static function flatten(array $array, string $prefix = ''): array
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            $key = $prefix . $key;
            
            if (is_array($value) && self::isAssociative($value)) {
                $result = array_merge(
                    $result,
                    self::flatten($value, $key . '.')
                );
            } else {
                $result[$key] = $value;
            }
        }
        
        return $result;
    }
    
    /**
     * Filter array by key/value pairs
     *
     * @param array $items Array to filter
     * @param array $filters Key=>value pairs for filtering
     * @return array Filtered array
     */
    public static function filter(array $items, array $filters): array
    {
        if (empty($filters)) return $items;
        
        return array_filter($items, function ($item) use (&$filters) {
            foreach ($filters as $key => $expected) {
                $actual = self::get($item, $key);
                
                if (is_array($expected)) {
                    if (!in_array($actual, $expected)) return false;
                } else {
                    if ($actual != $expected) return false;
                }
            }
            return true;
        });
    }
    
    /**
     * Group array items by a specific field
     *
     * @param array $items Array to group
     * @param string $field Field to group by
     * @return array Grouped array [field_value => [items...]]
     */
    public static function groupBy(array $items, string $field): array
    {
        $groups = [];
        
        foreach ($items as $item) {
            $value = self::get($item, $field, '');
            $groups[$value][] = $item;
        }
        
        ksort($groups);
        
        return $groups;
    }
    
    /**
     * Pluck first N elements from array
     */
    public static function take(array &$items, int $count = 1): array
    {
        return array_splice($items, 0, $count);
    }
    
    /**
     * Map array values using callback
     *
     * @param array $items Source array
     * @param callable $callback Callback function($item, $key)
     * @return array Mapped array
     */
    public static function map(array $items, callable $callback): array
    {
        return array_map($callback, $items);
    }
    
    /**
     * Convert object/array to array
     */
    public static function toArray(mixed $data): array
    {
        if (is_object($data) && method_exists($data, 'toArray')) {
            return $data->toArray();
        }
        
        if (is_object($data)) {
            return get_object_vars($data);
        }
        
        return (array)$data;
    }
    
    /**
     * Pick random element from array
     */
    public static function random(array $array)
    {
        return $array[array_rand($array)];
    }
    
    /**
     * Shuffle array in place
     */
    public static function shuffle(array &$array): bool
    {
        shuffle($array);
        return true;
    }
    
    /**
     * Merge multiple arrays recursively
     */
    public static function merge(array ...$arrays): array
    {
        $result = [];
        
        foreach ($arrays as $array) {
            $result = array_merge($result, self::toArray($array));
        }
        
        return $result;
    }
    
    /**
     * Sort array by field ascending
     */
    public static function sortAsc(array &$array, string $field = 'id'): void
    {
        usort($array, fn ($a, $b) => strcmp(self::get($a, $field, ''), self::get($b, $field, '')));
    }
    
    /**
     * Sort array by field descending
     */
    public function sortDesc(array &$array, string $field = 'id'): void
    {
        usort($array, fn ($a, $b) => strcmp(self::get($b, $field, ''), self::get($a, $field, '')));
    }
}