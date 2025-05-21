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
 *  @file Uuid.php
 *
 * The UUID helper class
 *
 * The following class generates VALID RFC 4211 COMPLIANT Universally Unique IDentifiers (UUID)
 * version 3, 4 and 5.
 * Version 3 and 5 UUIDs are named based. They require a name space (another valid UUID) and a value
 * (the name). Given the same name space and name, the output is always the same.
 * Version 4 UUIDs are pseudo-random.
 * UUIDs generated below validates using OSSP UUID Tool, and output for named-based UUIDs
 * are exactly the same. This is a pure PHP implementation.
 *
 * @see "Andrew Moore" contribution note on https://www.php.net/manual/en/function.uniqid.php
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
 * @class Uuid
 * @package Platine\Stdlib\Helper
 */
class Uuid
{
    /**
     * Generate UUID v3
     * @param string $namespace
     * @param string $name
     * @return string
     */
    public static function v3(string $namespace, string $name): string
    {
        return self::v3v5($namespace, $name, 'v3');
    }

    /**
     * Generate UUID v4
     * @return string
     */
    public static function v4(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * Generate UUID v5
     * @param string $namespace
     * @param string $name
     * @return string
     */
    public static function v5(string $namespace, string $name): string
    {
        return self::v3v5($namespace, $name, 'v5');
    }


    /**
     * Whether the given UUID is valid
     * @param string $uuid
     * @return bool
     */
    public static function isValid(string $uuid): bool
    {
        return preg_match(
            '/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i',
            $uuid
        ) === 1;
    }

    /**
     * Generate UUID v3 and v5
     * @param string $namespace
     * @param string $name
     * @param string $type can be v3 or v5
     * @return string
     */
    protected static function v3v5(string $namespace, string $name, string $type = 'v3'): string
    {
        if (self::isValid($namespace) === false) {
            throw new InvalidArgumentException(sprintf('Invalid namespace [%s] provided', $namespace));
        }

        // Get hexadecimal components of namespace
        $nhex = str_replace(['-', '{', '}'], '', $namespace);

        // Binary Value
        $binaryStr = '';

        // Convert Namespace UUID to bits
        for ($i = 0; $i < strlen($nhex); $i += 2) {
            $binaryStr .= chr(hexdec($nhex[$i] . $nhex[$i + 1]));
        }

        // Calculate hash value
        if ($type === 'v3') {
            $hash = md5($binaryStr . $name);
        } else {
            $hash = sha1($binaryStr . $name);
        }

        return sprintf(
            '%08s-%04s-%04x-%04x-%12s',
            // 32 bits for "time_low"
            substr($hash, 0, 8),
            // 16 bits for "time_mid"
            substr($hash, 8, 4),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 3 and 5
            $type === 'v3' ?
                (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000 : (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1

            (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,
            // 48 bits for "node"
            substr($hash, 20, 12)
        );
    }
}
