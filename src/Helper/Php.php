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
 *  @file Php.php
 *
 *  The PHP helper class
 *
 *  @package    Platine\Stdlib\Helper
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Stdlib\Helper;

use Throwable;

/**
 * Class Php
 * @package Platine\Stdlib\Helper
 */
class Php
{

    /**
     * Call by callback
     * @param callable|array<mixed> $callback
     * @param mixed ...$args
     *
     * @return mixed
     */
    public static function call($callback, ...$args)
    {
        if (is_string($callback)) {
            // className::method
            if (strpos($callback, '::') > 0) {
                $callback = explode('::', $callback, 2);
            } elseif (function_exists($callback)) { //function
                return $callback(...$args);
            }
        } elseif (is_object($callback) && method_exists($callback, '__invoke')) {
            return $callback(...$args);
        }

        if (is_array($callback)) {
            [$obj, $method] = $callback;

            return is_object($obj)
                    ? $obj->{$method}(...$args)
                    : $obj::$method(...$args);
        }

        return $callback(...$args);
    }

    /**
     * Call by array
     * @param callable|array<mixed> $callback
     * @param array<int, mixed> $args
     *
     * @return mixed
     */
    public static function callArray($callback, array $args)
    {
        return self::call($callback, ...$args);
    }

    /**
     * Dump variables
     * @param mixed ...$vars
     * @return string
     */
    public static function dumpVars(...$vars): string
    {
        ob_start();
        var_dump(...$vars);

        $string = (string) ob_get_clean();

        return (string) preg_replace("/=>\n\s+/", '=> ', $string);
    }

    /**
     * Print variables
     * @param mixed ...$vars
     * @return string
     */
    public static function printVars(...$vars): string
    {
        $string = '';
        foreach ($vars as $var) {
            $string .= print_r($var, true) . PHP_EOL;
        }

        return (string) preg_replace("/Array\n\s+\(/", 'Array (', $string);
    }

    /**
     * Export variable
     * @param mixed $var
     * @return string
     */
    public static function exportVar($var): string
    {
        $string = var_export($var, true);

        return (string) preg_replace(
            '/=>\s+\n\s+array \(/',
            '=> array (',
            $string
        );
    }

    /**
     * Convert an Exception to string
     * @param Throwable $err
     * @param string $title
     * @param bool $debug
     * @return string
     */
    public static function exceptionToString(
        Throwable $err,
        string $title = '',
        bool $debug = false
    ): string {
        $className = get_class($err);
        if (!$debug) {
            return sprintf(
                '%s %s(code:%d) %s',
                $title,
                $className,
                $err->getCode(),
                $err->getMessage()
            );
        }

        return sprintf(
            '%s%s(code:%d) %s at %s line %d',
            $title ? $title . '-' : '',
            $className,
            $err->getCode(),
            $err->getMessage(),
            $err->getFile(),
            $err->getLine()
        );
    }

    /**
     * Convert an Exception to array
     * @param Throwable $err
     * @param bool $debug
     * @return array<string, mixed>
     */
    public static function exceptionToArray(
        Throwable $err,
        bool $debug = false
    ): array {
        if (!$debug) {
            return [
                'code' => $err->getCode(),
                'error' => $err->getMessage()
            ];
        }

        return [
                'code' => $err->getCode(),
                'error' => sprintf('(%s) %s', get_class($err), $err->getMessage()),
                'file' => sprintf('at %s line %d', $err->getFile(), $err->getLine()),
                'trace' => $err->getTraceAsString(),
            ];
    }
}
