<?php

/**
 * Platine Stdlib
 *
 * Platine Stdlib is a the collection of frequently used php features
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine Stdlib
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 *  @file Arr.php
 *
 *  The Array helper class
 *
 *  @package    Platine\Stdlib\Helper
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Stdlib\Helper;

use ArrayAccess;
use Closure;
use InvalidArgumentException;
use Platine\Stdlib\Contract\Arrayable;
use Stringable;
use Traversable;


/**
 * @class Arr
 * @package Platine\Stdlib\Helper
 */
class Arr
{
    /**
     * Convert an array, object or string to array
     * @param array<mixed>|object|string $object
     * @param array<string, array<int|string, string>> $properties
     * @param bool $recursive
     * @return array<mixed>
     */
    public static function toArray(
        array|object|string $object,
        array $properties = [],
        bool $recursive = true
    ): array {
        if (is_array($object)) {
            if ($recursive) {
                foreach ($object as $key => $value) {
                    if (is_array($value) || is_object($value)) {
                        $object[$key] = static::toArray($value, $properties, true);
                    }
                }
            }

            return $object;
        }

        if (is_object($object)) {
            if (count($properties) > 0) {
                $className = get_class($object);
                if (count($properties[$className]) > 0) {
                    $result = [];
                    foreach ($properties[$className] as $key => $name) {
                        if (is_int($key)) {
                            $result[$name] = $object->{$name};
                        } else {
                            $result[$key] = static::getValue($object, $name);
                        }
                    }

                    return $recursive
                            ? static::toArray($result, $properties)
                            : $result;
                }
            }

            if ($object instanceof Arrayable) {
                $result = $object->toArray();
            } else {
                $result = [];
                foreach ((array)$object as $key => $value) {
                    $result[$key] = $value;
                }
            }

            return $recursive
                    ? static::toArray($result, $properties)
                    : $result;
        }

        return [$object];
    }

    /**
     * Merge the passed arrays
     * @param array<mixed> ...$args
     * @return array<mixed>
     */
    public static function merge(array ...$args): array
    {
        $res = (array)array_shift($args);
        while (count($args) > 0) {
            $next = array_shift($args);
            foreach ($next as $key => $value) {
                if (is_int($key)) {
                    if (isset($res[$key])) {
                        $res[] = $value;
                    } else {
                        $res[$key] = $value;
                    }
                } elseif (is_array($value) && isset($res[$key]) && is_array($res[$key])) {
                    $res[$key] = self::merge($res[$key], $value);
                } else {
                    $res[$key] = $value;
                }
            }
        }

        return $res;
    }

    /**
     * Return the value of an array element or object property
     * for the given key or property name.
     *
     * @param mixed $object
     * @param int|string|Closure|array<mixed> $key
     * @param mixed $default
     *
     * @return mixed If the key does not exist in the array or object,
     * the default value will be returned instead.
     */
    public static function getValue(
        mixed $object,
        int|string|Closure|array $key,
        mixed $default = null
    ): mixed {
        if ($key instanceof Closure) {
            return $key($object, $default);
        }

        if (is_array($key)) {
            $lastKey = array_pop($key);
            foreach ($key as $part) {
                $object = static::getValue($object, $part);
            }
            $key = $lastKey;
        }

        if (
            is_array($object) && (isset($object[$key])
                || array_key_exists($key, $object))
        ) {
            return $object[$key];
        }

        if (is_string($key)) {
            $pos = strpos($key, '.');

            if ($pos !== false) {
                $object = static::getValue(
                    $object,
                    substr($key, 0, $pos),
                    $default
                );
                $key = (string) substr($key, $pos + 1);
            }
        }

        // Note: property_exists not detected property with magic
        // Method so add isset for this purpose
        if (is_object($object) && (property_exists($object, $key) || isset($object->{$key}))) {
            // this is will fail if the property does not exist,
            //  or __get() is not implemented
            // it is not reliably possible to check whether a property
            // is accessable beforehand

            return $object->{$key};
        }

        if (is_array($object)) {
            return (isset($object[$key]) || array_key_exists($key, $object))
                    ? $object[$key]
                    : $default;
        }

        return $default;
    }

    /**
     * Remove an item from an array and returns the value.
     * If the key does not exist in the array, the default value will be returned
     * @param array<mixed> $array
     * @param string|int $key
     * @param mixed $default
     *
     * @return mixed|null
     */
    public static function remove(array &$array, string|int $key, mixed $default = null): mixed
    {
        if (isset($array[$key]) || array_key_exists($key, $array)) {
            $value = $array[$key];

            unset($array[$key]);

            return $value;
        }

        return $default;
    }

    /**
     * Return all of the given array except for a specified keys.
     * @param array<mixed> $array
     * @param array<int, int|string>|string|int $keys
     * @return array<mixed>
     */
    public static function except(array $array, array|string|int $keys): array
    {
        static::forget($array, $keys);

        return $array;
    }

    /**
     * Remove one or many array items from a given array using "dot" notation.
     * @param array<mixed> $array
     * @param array<int, int|string>|string|int $keys
     * @return void
     */
    public static function forget(array &$array, array|string|int $keys): void
    {
        $original = &$array;

        if (!is_array($keys)) {
            $keys = [$keys];
        }

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            if (static::exists($array, $key)) {
                unset($array[$key]);

                continue;
            }

            if (is_string($key)) {
                $parts = explode('.', $key);

                $array = &$original;
                while (count($parts) > 1) {
                    $part = array_shift($parts);
                    if (isset($array[$part]) && is_array($array[$part])) {
                        $array = &$array[$part];
                    } else {
                        continue 2;
                    }
                }

                unset($array[array_shift($parts)]);
            }
        }
    }

    /**
     * Get a value from the array, and remove it.
     * @param array<mixed> $array
     * @param string|int $key
     * @param mixed $default
     * @return mixed
     */
    public static function pull(array &$array, string|int $key, mixed $default = null): mixed
    {
        $value = static::get($array, $key, $default);

        static::forget($array, $key);

        return $value;
    }

    /**
     * Indexes and/or groups the array according to a specified key.
     * The input should be either multidimensional array or an array of objects.
     *
     * The $key can be either a key name of the sub-array, a property
     * name of object, or an anonymous function that must return the value
     * that will be used as a key.
     *
     * $groups is an array of keys, that will be used to group the input
     * array into one or more sub-arrays based on keys specified.
     *
     * If the `$key` is specified as `null` or a value of an element
     * corresponding to the key is `null` in addition to `$groups` not
     * specified then the element is discarded.
     *
     * @param array<mixed> $array
     * @param string|int|Closure|array<mixed>|null $key
     * @param string|int|string[]|int[]|Closure[]|null $groups
     * @return array<mixed> the indexed and/or grouped array
     */
    public static function index(
        array $array,
        string|int|Closure|array|null $key = null,
        string|int|array|null $groups = []
    ): array {
        $result = [];
        if (!is_array($groups)) {
            $groups = (array) $groups;
        }

        foreach ($array as $element) {
            $lastArray = &$result;
            foreach ($groups as $group) {
                /** @var int|string $value */
                $value = static::getValue($element, $group);
                if (count($lastArray) > 0 && !array_key_exists($value, $lastArray)) {
                    $lastArray[$value] = [];
                }
                $lastArray = &$lastArray[$value];
            }

            if ($key === null) {
                if (count($groups) > 0) {
                    $lastArray[] = $element;
                }
            } else {
                $value = static::getValue($element, $key);
                if ($value !== null) {
                    if (is_float($value)) {
                        $value = (string) $value;
                    }
                    $lastArray[$value] = $element;
                }
            }
            unset($lastArray);
        }

        return $result;
    }

    /**
     * Returns the values of a specified column in an array.
     * The input array should be multidimensional or an array of objects.
     * @param array<mixed> $array
     * @param int|string|Closure $name
     * @param bool $keepKeys
     * @return array<mixed>
     */
    public static function getColumn(
        array $array,
        int|string|Closure $name,
        bool $keepKeys = true
    ): array {
        $result = [];
        if ($keepKeys) {
            foreach ($array as $key => $element) {
                $result[$key] = static::getValue($element, $name);
            }
        } else {
            foreach ($array as $element) {
                $result[] = static::getValue($element, $name);
            }
        }

        return $result;
    }

    /**
     * Builds a map (key-value pairs) from a multidimensional array or
     * an array of objects.
     * The `$from` and `$to` parameters specify the key names or property
     * names to set up the map.
     * Optionally, one can further group the map according to a
     * grouping field `$group`.
     *
     * @param array<mixed> $array
     * @param string|Closure $from
     * @param string|Closure $to
     * @param string|array<mixed>|Closure|null $group
     * @return array<mixed>
     */
    public static function map(
        array $array,
        string|Closure $from,
        string|Closure $to,
        string|array|Closure|null $group = null
    ): array {
        $result = [];
        foreach ($array as $element) {
            $key = static::getValue($element, $from);
            $value = static::getValue($element, $to);
            if ($group !== null) {
                $result[static::getValue($element, $group)][$key] = $value;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Checks if the given array contains the specified key.
     * This method enhances the `array_key_exists()` function by supporting
     * case-insensitive key comparison.
     *
     * @param string $key
     * @param array<mixed> $array
     * @param bool $caseSensative
     * @return bool
     */
    public static function keyExists(
        string $key,
        array $array,
        bool $caseSensative = true
    ): bool {
        if ($caseSensative) {
            return array_key_exists($key, $array);
        }

        foreach (array_keys($array) as $k) {
            if (strcasecmp($key, $k) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sorts an array of objects or arrays (with the same structure) by one
     * or several keys.
     * @param array<mixed> $array
     * @param string|Closure|array<string> $key the key(s) to be sorted by.
     *     This refers to a key name of the sub-array elements, a property name
     *      of the objects, or an anonymous function returning the values
     *   for comparison purpose. The anonymous function signature
     * should be: `function($item)`.
     * @param int|array<int> $direction
     * @param int|array<int> $sortFlag
     * @return void
     */
    public static function multisort(
        array &$array,
        string|Closure|array $key,
        int|array $direction = SORT_ASC,
        int|array $sortFlag = SORT_REGULAR
    ): void {
        $keys = is_array($key) ? $key : [$key];

        if (empty($keys) || empty($array)) {
            return;
        }

        $count  = count($keys);
        if (is_scalar($direction)) {
            $direction = array_fill(0, $count, $direction);
        } elseif (count($direction) !== $count) {
            throw new InvalidArgumentException(
                'The length of the sort direction must be the same '
                    . 'as that of sort keys.'
            );
        }
        if (is_scalar($sortFlag)) {
            $sortFlag = array_fill(0, $count, $sortFlag);
        } elseif (count($sortFlag) !== $count) {
            throw new InvalidArgumentException(
                'The length of the sort flag must be the same '
                    . 'as that of sort keys.'
            );
        }

        /** @var array<int, mixed> $args */
        $args = [];
        foreach ($keys as $i => $k) {
            $flag = $sortFlag[$i];
            $args[] = static::getColumn($array, $k);
            $args[] = $direction[$i];
            $args[] = $flag;
        }

        // This fix is used for cases when main sorting specified by
        // columns has equal values
        // Without it it will lead to Fatal Error: Nesting level
        // too deep - recursive dependency?
        $args[] = range(1, count($array));
        $args[] = SORT_ASC;
        $args[] = SORT_NUMERIC;
        $args[] = &$array;

        array_multisort(...$args);
    }

    /**
     * Check whether the given array is an associative array.
     *
     * An array is associative if all its keys are strings.
     * If `$allStrings` is false, then an array will be treated as associative
     * if at least one of its keys is a string.
     *
     * Note that an empty array will NOT be considered associative.
     *
     * @param array<mixed> $array
     * @param bool $allStrings
     * @return bool
     */
    public static function isAssoc(array $array, bool $allStrings = true): bool
    {
        if (empty($array)) {
            return false;
        }

        if ($allStrings) {
            foreach ($array as $key => $value) {
                if (!is_string($key)) {
                    return false;
                }
            }

            return true;
        } else {
            foreach ($array as $key => $value) {
                if (is_string($key)) {
                    return true;
                }
            }

            return false;
        }
    }

    /**
     * Whether the given array is an indexed array.
     *
     * An array is indexed if all its keys are integers.
     * If `$consecutive` is true, then the array keys must be a consecutive
     * sequence starting from 0.
     *
     * Note that an empty array will be considered indexed.
     *
     * @param array<mixed> $array
     * @param bool $consecutive
     * @return bool
     */
    public static function isIndexed(array $array, bool $consecutive = false): bool
    {
        if (empty($array)) {
            return true;
        }

        if ($consecutive) {
            return array_keys($array) === range(0, count($array) - 1);
        } else {
            foreach ($array as $key => $value) {
                if (!is_int($key)) {
                    return false;
                }
            }

            return true;
        }
    }

    /**
     * Check whether an array or Traversable contains an element.
     *
     * This method does the same as the PHP function in_array()
     * but additionally works for objects that implement the
     * Traversable interface.
     *
     * @param mixed $needle
     * @param array<mixed>|Traversable<int|string, mixed> $array
     * @param bool $strict
     * @return bool
     */
    public static function isIn(mixed $needle, array|Traversable $array, bool $strict = false): bool
    {
        if ($array instanceof Traversable) {
            $array = iterator_to_array($array);
        }

        foreach ($array as $value) {
            if ($needle == $value && (!$strict || $needle === $value)) {
                return true;
            }
        }


        return in_array($needle, $array, $strict);
    }

    /**
     * Checks whether a variable is an array or Traversable.
     * @param mixed $var
     * @return bool
     */
    public static function isTraversable(mixed $var): bool
    {
        return is_array($var) || $var instanceof Traversable;
    }

    /**
     * Checks whether an array or Traversable is a subset of another array
     * or Traversable.
     *
     * This method will return `true`, if all elements of `$needles`
     * are contained in `$array`. If at least one element is missing,
     * `false` will be returned.
     *
     * @param array<mixed>|Traversable<int|string, mixed> $needles
     * @param array<mixed>|Traversable<int|string, mixed> $array
     * @param bool $strict
     * @return bool
     */
    public static function isSubset(
        array|Traversable $needles,
        array|Traversable $array,
        bool $strict = false
    ): bool {
        foreach ($needles as $needle) {
            if (!static::isIn($needle, $array, $strict)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Filters array according to rules specified.
     * @param array<mixed> $array
     * @param array<string> $filters Rules that define array keys which should
     *  be left or removed from results.
     *         Each rule is:
     *            - `var` - `$array['var']` will be left in result.
     *            - `var.key` = only `$array['var']['key'] will be left in result.
     *            - `!var.key` = `$array['var']['key'] will be removed from result.
     * @return array<mixed>
     */
    public static function filter(array $array, array $filters): array
    {
        $result = [];
        $tobeRemoved = [];

        foreach ($filters as $filter) {
            $keys = explode('.', $filter);
            $globalKey = $keys[0];
            $localkey = $keys[1] ?? null;
            if ($globalKey[0] === '!') {
                $tobeRemoved[] = [
                    substr($globalKey, 1),
                    $localkey
                ];

                continue;
            }

            if (empty($array[$globalKey])) {
                continue;
            }

            if ($localkey === null) {
                $result[$globalKey] = $array[$globalKey];
                continue;
            }

            if (!isset($array[$globalKey][$localkey])) {
                continue;
            }

            if (array_key_exists($globalKey, $result)) {
                $result[$globalKey] = [];
            }

            $result[$globalKey][$localkey] = $array[$globalKey][$localkey];
        }

        foreach ($tobeRemoved as $value) {
            [$globalKey, $localkey] = $value;
            if (array_key_exists($globalKey, $result)) {
                unset($result[$globalKey][$localkey]);
            }
        }

        return $result;
    }

    /**
     * Checks whether a variable is an array accessible.
     * @param mixed $var
     * @return bool
     */
    public static function isAccessible(mixed $var): bool
    {
        return is_array($var) || $var instanceof ArrayAccess;
    }

    /**
     * Checks whether a variable is an array or instance of Arrayable.
     * @param mixed $var
     * @return bool
     */
    public static function isArrayable(mixed $var): bool
    {
        return is_array($var) || $var instanceof Arrayable;
    }

    /**
     * If the given value is not an array and not null, wrap it in one.
     * @param mixed $var
     * @return array<mixed>
     */
    public static function wrap(mixed $var): array
    {
        if ($var === null) {
            return [];
        }
        return is_array($var) ? $var : [$var];
    }

    /**
     * Check whether the given key exists in the provided array.
     *
     * @param array<mixed>|ArrayAccess<string|int, mixed> $array
     * @param string|int $key
     * @return bool
     */
    public static function exists(array|ArrayAccess $array, string|int $key): bool
    {
        if (is_array($array)) {
            return array_key_exists($key, $array);
        }

        return $array->offsetExists($key);
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param array<mixed>|ArrayAccess<string|int, mixed> $array
     * @param string|int|null $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(
        array|ArrayAccess $array,
        string|int|null $key = null,
        mixed $default = null
    ): mixed {
        if ($key === null) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        // Fix: If is int, stop continue find.
        if (!is_string($key)) {
            return $default;
        }

        foreach (explode('.', $key) as $segment) {
            if (static::isAccessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * Check if an item exists in an array using "dot" notation.
     *
     * @param array<mixed>|ArrayAccess<string|int, mixed> $array
     * @param string|int $key
     * @return bool
     */
    public static function has(array|ArrayAccess $array, string|int $key): bool
    {
        if (empty($array)) {
            return false;
        }

        if (
            (is_array($array) && array_key_exists($key, $array))
            || ($array instanceof ArrayAccess && $array->offsetExists($key))
        ) {
            return true;
        }

        // Fix: If is int, stop continue find.
        if (!is_string($key)) {
            return false;
        }

        foreach (explode('.', $key) as $segment) {
            if (
                ((is_array($array) && array_key_exists($segment, $array))
                || ($array instanceof ArrayAccess
                        && $array->offsetExists($segment)))
            ) {
                $array = $array[$segment];
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     *
     * @param array<mixed> $array
     * @param string|null $key
     * @param mixed $value
     * @return void
     */
    public static function set(array &$array, ?string $key, mixed $value): void
    {
        if ($key === null) {
            return;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create
            // an empty array hold the next value, allowing us to create
            // the arrays to hold final values at the correct depth.
            // Then we'll keep digging into the array.
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;
    }

    /**
     * Insert one array to another array
     *
     * @param array<mixed> $array
     * @param int $index
     * @param array<mixed> ...$inserts
     * @return void
     */
    public static function insert(
        array &$array,
        int $index,
        array ...$inserts
    ): void {
        $first = array_splice($array, 0, $index);
        $array = array_merge($first, $inserts, $array);
    }

    /**
     * Flatten a multi-dimensional array into a single level.
     * @param array<mixed> $array
     * @param int $depth
     * @return array<mixed>
     */
    public static function flatten(array $array, int $depth = PHP_INT_MAX): array
    {
        $result = [];
        foreach ($array as $item) {
            if (!is_array($item)) {
                $result[] = $item;
            } elseif ($depth === 1) {
                $result = array_merge($result, array_values($item));
            } else {
                $result = array_merge(
                    $result,
                    static::flatten($item, $depth - 1)
                );
            }
        }

        return $result;
    }

    /**
     * find similar text from an array
     *
     * @param string $need
     * @param array<int, string>|Traversable<int|string, mixed> $array
     * @param int $percentage
     * @return array<int, string>
     */
    public static function similar(
        string $need,
        array|Traversable $array,
        int $percentage = 45
    ): array {
        if (empty($need)) {
            return [];
        }

        $similar = [];
        $percent = 0;
        foreach ($array as $name) {
            similar_text($need, $name, $percent);
            if ($percentage <= (int) $percent) {
                $similar[] = $name;
            }
        }

        return $similar;
    }

    /**
     * Return the array key max width
     * @param array<int|string, mixed> $array
     * @param bool $expectInt
     * @return int
     */
    public static function getKeyMaxWidth(array $array, bool $expectInt = false): int
    {
        $max = 0;
        foreach ($array as $key => $value) {
            if (!$expectInt || !is_numeric($key)) {
                $width = mb_strlen((string)$key, 'UTF-8');
                if ($width > $max) {
                    $max = $width;
                }
            }
        }

        return $max;
    }

    /**
     * Return the first element in an array passing a given truth test.
     * @param array<mixed> $array
     * @param callable|null $callable
     * @param mixed $default
     *
     * @return mixed
     */
    public static function first(
        array $array,
        ?callable $callable = null,
        mixed $default = null
    ): mixed {
        if ($callable === null) {
            if (count($array) === 0) {
                return $default;
            }

            foreach ($array as $value) {
                return $value;
            }
        }

        foreach ($array as $key => $value) {
            if ($callable($value, $key)) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Return the last element in an array passing a given truth test.
     * @param array<mixed> $array
     * @param callable|null $callable
     * @param mixed $default
     *
     * @return mixed
     */
    public static function last(
        array $array,
        ?callable $callable = null,
        mixed $default = null
    ): mixed {
        if ($callable === null) {
            if (count($array) === 0) {
                return $default;
            }

            return end($array);
        }

        return static::first(array_reverse($array, true), $callable, $default);
    }

    /**
     * Filter the array using the given callback.
     * @param array<mixed> $array
     * @param callable $callable
     * @return array<mixed>
     */
    public static function where(array $array, callable $callable): array
    {
        return array_filter($array, $callable, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Convert the array into a query string.
     * @param array<mixed> $array
     * @return string
     */
    public static function query(array $array): string
    {
        return http_build_query($array, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Get a subset of the items from the given array.
     * @param array<mixed> $array
     * @param array<int, int|string> $keys
     * @return array<mixed>
     */
    public static function only(array $array, array $keys): array
    {
        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * Pluck an array of values from an arrays.
     * @param array<int, mixed> $array
     * @param string|int $value
     * @param string|int|null $key
     * @return array<mixed>
     */
    public static function pluck(
        array $array,
        string|int $value,
        string|int|null $key = null
    ): array {
        $results = [];
        foreach ($array as $item) {
            if (is_array($item)) {
                $itemValue = static::get($item, $value);

                // If the key is "null", we will just append the value to the array
                // and keep looping. Otherwise we will key the array using
                // the value of the key we received from the developer.
                // Then we'll return the final array form.
                if ($key === null) {
                    $results[] = $itemValue;
                } else {
                    $itemKey = static::get($item, $key);
                    if (is_object($itemKey) && $itemKey instanceof Stringable) {
                        $itemKey = (string)$itemKey;
                    }

                    $results[$itemKey] = $itemValue;
                }
            }
        }

        return $results;
    }

    /**
     * Collapse an array of arrays into a single array.
     * @param array<mixed> $array
     * @return array<mixed>
     */
    public static function collapse(array $array): array
    {
        $results = [];
        foreach ($array as $values) {
            if (!is_array($values)) {
                continue;
            }

            $results = array_merge($results, $values);
        }

        return $results;
    }

    /**
     * Cross join the given arrays, returning all possible permutations.
     * @param array<mixed> ...$arrays
     * @return array<mixed>
     */
    public static function crossJoin(array ...$arrays): array
    {
        $results = [[]];
        foreach ($arrays as $index => $array) {
            $append = [];

            foreach ($results as $product) {
                foreach ($array as $item) {
                    $product[$index] = $item;

                    $append[] = $product;
                }
            }

            $results = $append;
        }

        return $results;
    }

    /**
     *
     * @param array<int|string, mixed> $array
     * @param mixed $value
     * @param mixed $key
     * @return array<mixed>
     */
    public static function prepend(array $array, mixed $value, mixed $key = null): array
    {
        if ($key === null) {
            array_unshift($array, $value);
        } else {
            $array = [$key => $value] + $array;
        }

        return $array;
    }

    /**
     * Get one or a specified number of random values from an array.
     * @param array<mixed> $array
     * @param int|null $number
     *
     * @return mixed
     */
    public static function random(array $array, ?int $number = null): mixed
    {
        $requested = $number === null ? 1 : $number;
        $count = count($array);

        if ($requested > $count) {
            throw new InvalidArgumentException(sprintf(
                'You requested %d items, but there are only %d items available',
                $requested,
                $count
            ));
        }

        if ($number === null) {
            return $array[array_rand($array)];
        }

        if ($number === 0) {
            return [];
        }

        $keys = array_rand($array, $number);

        $results = [];
        foreach ((array)$keys as $key) {
            $results[] = $array[$key];
        }

        return $results;
    }

    /**
     * Convert an array to string
     * @param array<mixed> $array
     * @param string $glue
     * @return string
     */
    public static function toString(array $array, string $glue = '_'): string
    {
        return implode($glue, $array);
    }

    /**
     * Shuffle the given array and return the result.
     * @param array<mixed> $array
     * @param int|null $seed
     * @return array<mixed>
     */
    public static function shuffle(array $array, ?int $seed = null): array
    {
        if ($seed === null) {
            shuffle($array);
        } else {
            mt_srand($seed);
            shuffle($array);
            mt_srand();
        }

        return $array;
    }

    /**
     * Normalize command line arguments like splitting "-abc" and "--xyz=...".
     * @param array<int, string> $args
     * @return array<string>
     */
    public static function normalizeArguments(array $args): array
    {
        $normalized = [];
        foreach ($args as $arg) {
            if (preg_match('/^\-\w=/', $arg)) {
                $normalized = array_merge(
                    $normalized,
                    (array)explode('=', $arg)
                );
            } elseif (preg_match('/^\-\w{2,}/', $arg)) {
                $splitArgs = implode(' -', str_split(ltrim($arg, '-')));
                $normalized = array_merge(
                    $normalized,
                    (array)explode(' ', '-' . $splitArgs)
                );
            } elseif (preg_match('/^\-\-([^\s\=]+)\=/', $arg)) {
                $normalized = array_merge(
                    $normalized,
                    (array)explode('=', $arg)
                );
            } else {
                $normalized[] = $arg;
            }
        }

        return $normalized;
    }
}
