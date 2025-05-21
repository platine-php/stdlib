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
 *  @file Json.php
 *
 *  The JSON helper class
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

use InvalidArgumentException;

/**
 * @class Json
 * @package Platine\Stdlib\Helper
 */
class Json
{
    /**
     * Decode JSON string
     * @param string $json
     * @param bool $assoc
     * @param int $depth
     * @param int $options
     *
     * @return array<mixed>|object|bool|null
     */
    public static function decode(
        string $json,
        bool $assoc = false,
        int $depth = 512,
        int $options = 0
    ): array|object|bool|null {
        if ($depth < 1 || $depth > PHP_INT_MAX) {
            $depth = 512;
        }

        $data = json_decode($json, $assoc, $depth, $options);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException(
                'Error when decoded json string: ' . json_last_error_msg()
            );
        }

        return $data;
    }

    /**
     * Encode to JSON the given data
     * @param mixed $data
     * @param int $options
     * @param int $depth
     * @return string
     */
    public static function encode(
        mixed $data,
        int $options = 0,
        int $depth = 512
    ): string {
        if ($depth < 1 || $depth > PHP_INT_MAX) {
            $depth = 512;
        }

        $json = json_encode($data, $options, $depth);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException(
                'Error when encoded json data: ' . json_last_error_msg()
            );
        }

        return (string) $json;
    }
}
