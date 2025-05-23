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
 *  @file Unit.php
 *
 *  The Unit helper class
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

/**
 * @class Unit
 * @package Platine\Stdlib\Helper
 */
class Unit
{
    /**
     * Convert the size like 4G, 7T, 19B to byte
     * @param string $size
     * @return int
     */
    public static function sizeInBytes(string $size): int
    {
        $value = 1;
        $unit = 'B';
        $units = ['B' => 0, 'K' => 1, 'M' => 2, 'G' => 3, 'T' => 4];
        $matches = [];
        preg_match('/(?<size>[\d\.]+)\s*(?<unit>b|k|m|g|t)?/i', $size, $matches);
        if (array_key_exists('unit', $matches)) {
            $unit = strtoupper($matches['unit']);
        }

        if (array_key_exists('size', $matches)) {
            $value = floatval(strtoupper($matches['size']));
        }
        return (int)($value * pow(1024, $units[$unit]));
    }

    /**
     * Format to human readable size
     * @param int $size
     * @param int $precision
     * @return string
     */
    public static function formatSize(int $size, int $precision = 2): string
    {
        if ($size > 0) {
            $base = log($size) / log(1024);
            $suffixes = ['B', 'K', 'M', 'G', 'T'];
            $suffix = '';
            if (isset($suffixes[floor($base)])) {
                $suffix = $suffixes[floor($base)];
            }
            return round(pow(1024, $base - floor($base)), $precision) . $suffix;
        }

        return '';
    }
}
