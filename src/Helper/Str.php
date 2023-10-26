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
 *  @file Str.php
 *
 *  The String helper class
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

use DateTime;
use DateTimeInterface;
use JsonSerializable;
use Throwable;
use Traversable;

/**
 * Class Str
 * @package Platine\Stdlib\Helper
 */
class Str
{
    /**
     * The cache of snake-cased words.
     *
     * @var array<string, string>
     */
    protected static array $snakeCache = [];

    /**
     * The cache of camel-cased words.
     *
     * @var array<string, string>
     */
    protected static array $camelCache = [];

    /**
     * The cache of studly-cased words.
     *
     * @var array<string, string>
     */
    protected static array $studlyCache = [];

    /**
     * Convert an UTF-8 value to ASCII.
     * @param string $value
     * @return string
     */
    public static function toAscii(string $value): string
    {
        foreach (self::getChars() as $key => $val) {
            $value = str_replace($val, $key, $value);
        }

        return (string)preg_replace('/[^\x20-\x7E]/u', '', $value);
    }

    /**
     * Convert to camel case
     * @param string $value
     * @param bool $lcfirst
     * @return string
     */
    public static function camel(string $value, bool $lcfirst = true): string
    {
        if (isset(self::$camelCache[$value])) {
            return self::$camelCache[$value];
        }

        $studly = static::studly($value);
        return self::$camelCache[$value] = ($lcfirst ? lcfirst($studly) : $studly);
    }

    /**
     * Convert an string to array
     * @param string $value
     * @param string $delimiter
     * @param int $limit
     * @return array<string>
     */
    public static function toArray(string $value, string $delimiter = ', ', int $limit = 0): array
    {
        $string = trim($value, $delimiter . ' ');
        if ($string === '') {
            return [];
        }

        $values = [];
        /** @var array<string> $rawList */
        $rawList = $limit < 1
                ? (array) explode($delimiter, $string)
                : (array) explode($delimiter, $string, $limit);

        foreach ($rawList as $val) {
            $val = trim($val);
            if ($val !== '') {
                $values[] = $val;
            }
        }

        return $values;
    }

    /**
     * Determine if a given string contains a given sub string.
     * @param string $value
     * @param string|array<mixed> $needles
     * @return bool
     */
    public static function contains(string $value, $needles): bool
    {
        if (!is_array($needles)) {
            $needles = [$needles];
        }

        foreach ($needles as $needle) {
            if ($needle !== '' && strpos($needle, $value) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string ends with a given sub string.
     * @param string $value
     * @param string|array<mixed> $needles
     * @return bool
     */
    public static function endsWith(string $value, $needles): bool
    {
        if (!is_array($needles)) {
            $needles = [$needles];
        }

        foreach ($needles as $needle) {
            if ($value === (string) substr($needle, -strlen($value))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string starts with a given sub string.
     * @param string $value
     * @param string|array<mixed> $needles
     * @return bool
     */
    public static function startsWith(string $value, $needles): bool
    {
        if (!is_array($needles)) {
            $needles = [$needles];
        }

        foreach ($needles as $needle) {
            if ($needle !== '' && strpos($needle, $value) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the first line of multi line string
     * @param string $value
     * @return string
     */
    public static function firstLine(string $value): string
    {
        $str = trim($value);

        if ($str === '') {
            return '';
        }

        if (strpos($str, "\n") > 0) {
            $parts = explode("\n", $str);

            return $parts[0] ?? '';
        }

        return $str;
    }

    /**
     * Cap a string with a single instance of a given value.
     * @param string $value
     * @param string $cap
     * @return string
     */
    public static function finish(string $value, string $cap): string
    {
        $quoted = preg_quote($cap, '/');

        return (string) preg_replace('/(?:' . $quoted . ')+$/', '', $value)
                . $cap;
    }

    /**
     * Determine if a given string matches a given pattern.
     * @param string $pattern
     * @param string $value
     * @return bool
     */
    public static function is(string $pattern, string $value): bool
    {
        if ($pattern === $value) {
            return true;
        }

        $quoted = preg_quote($pattern, '#');

        // Asterisks are translated into zero-or-more regular expression wildcards
        // to make it convenient to check if the strings starts with the given
        // pattern such as "library/*", making any string check convenient.
        $cleanQuoted = str_replace('\*', '.*', $quoted);

        return (bool)preg_match('#^' . $cleanQuoted . '\z#', $value);
    }

    /**
     * Return the length of the given string
     * @param string|int $value
     * @param string $encode
     * @return int
     */
    public static function length($value, string $encode = 'UTF-8'): int
    {
        if (!is_string($value)) {
            $value = (string) $value;
        }

        $length = mb_strlen($value, $encode);

        return $length !== false ? $length : -1;
    }


    /**
     * Add padding to string
     * @param string|int $value
     * @param int $length
     * @param string $padStr
     * @param int $type
     * @return string
     */
    public static function pad(
        $value,
        int $length,
        string $padStr = ' ',
        int $type = STR_PAD_BOTH
    ): string {
        if (!is_string($value)) {
            $value = (string) $value;
        }

        return $length > 0
                ? str_pad($value, $length, $padStr, $type)
                : $value;
    }

    /**
     * Add padding to string to left
     * @param string|int $value
     * @param int $length
     * @param string $padStr
     * @return string
     */
    public static function padLeft(
        $value,
        int $length,
        string $padStr = ' '
    ): string {
        return self::pad($value, $length, $padStr, STR_PAD_LEFT);
    }

    /**
     * Add padding to string to right
     * @param string|int $value
     * @param int $length
     * @param string $padStr
     * @return string
     */
    public static function padRight(
        $value,
        int $length,
        string $padStr = ' '
    ): string {
        return self::pad($value, $length, $padStr, STR_PAD_RIGHT);
    }

    /**
     * Repeat the given string $length times
     * @param string|int $value
     * @param int $length
     * @return string
     */
    public static function repeat($value, int $length = 1): string
    {
        if (!is_string($value)) {
            $value = (string) $value;
        }

        return str_repeat($value, $length);
    }

    /**
     * Limit the length of given string
     * @param string $value
     * @param int $length
     * @param string $end
     * @return string
     */
    public static function limit(string $value, int $length = 100, string $end = '...'): string
    {
        if (mb_strwidth($value, 'UTF-8') <= $length) {
            return $value;
        }

        return rtrim(mb_strimwidth($value, 0, $length, '', 'UTF-8')) . $end;
    }

    /**
     * Limit the number of words in a string.
     * @param string $value
     * @param int $length
     * @param string $end
     * @return string
     */
    public static function words(string $value, int $length = 100, string $end = '...'): string
    {
        $matches = [];
        preg_match('/^\s*+(?:\S++\s*+){1,' . $length . '}/u', $value, $matches);

        if (!isset($matches[0]) || strlen($value) === strlen($matches[0])) {
            return $value;
        }

        return rtrim($matches[0]) . $end;
    }

    /**
     * Replace the first match of the given string
     * @param string $search
     * @param string $replace
     * @param string $value
     * @return string
     */
    public static function replaceFirst(string $search, string $replace, string $value): string
    {
        $pos = strpos($value, $search);
        if ($pos !== false) {
            return substr_replace($value, $replace, $pos, strlen($search));
        }

        return $value;
    }

    /**
     * Replace the last match of the given string
     * @param string $search
     * @param string $replace
     * @param string $value
     * @return string
     */
    public static function replaceLast(string $search, string $replace, string $value): string
    {
        $pos = strrpos($value, $search);

        if ($pos !== false) {
            return substr_replace($value, $replace, $pos, strlen($search));
        }

        return $value;
    }

    /**
     * Put the string to title format
     * @param string $value
     * @return string
     */
    public static function title(string $value): string
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Generate a friendly "slug" from a given string.
     * @param string $value
     * @param string $separator
     * @return string
     */
    public static function slug(string $value, string $separator = '-'): string
    {
        $title = self::toAscii($value);

        // Convert all dashes/underscores into separator
        $flip = $separator === '-' ? '_' : '-';

        $utf8 = (string) preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $title);

        // Remove all characters that are not the separator, letters, numbers,
        // or whitespace.
        $alphaNum = (string) preg_replace(
            '![^' . preg_quote($separator) . '\pL\pN\s]+!u',
            '',
            mb_strtolower($utf8)
        );

        // Replace all separator characters and whitespace by a single separator
        $removeWhitespace = (string) preg_replace(
            '![' . preg_quote($separator) . '\s]+!u',
            $separator,
            $alphaNum
        );

        return trim($removeWhitespace, $separator);
    }

    /**
     * Convert a string to snake case.
     * @param string $value
     * @param string $separator
     * @return string
     */
    public static function snake(string $value, string $separator = '_'): string
    {
        $key = $value . $separator;
        if (isset(self::$snakeCache[$key])) {
            return self::$snakeCache[$key];
        }

        if (!ctype_lower($value)) {
            $replace = (string) preg_replace('/\s+/', '', $value);

            $value = strtolower((string) preg_replace(
                '/(.)(?=[A-Z])/',
                '$1' . $separator,
                $replace
            ));
        }

        return self::$snakeCache[$key] = $value;
    }

    /**
     * Convert a value to studly caps case.
     * @param string $value
     * @return string
     */
    public static function studly(string $value): string
    {
        $key = $value;
        if (isset(self::$studlyCache[$key])) {
            return self::$studlyCache[$key];
        }

        $val = ucwords(str_replace(['-', '_'], ' ', $value));

        return self::$studlyCache[$key] = str_replace(' ', '', $val);
    }

    /**
     * Returns the portion of string specified by the start and
     * length parameters.
     *
     * @param string $value
     * @param int $start
     * @param int|null $length
     * @return string
     */
    public static function substr(string $value, int $start = 0, ?int $length = null): string
    {
        return mb_substr($value, $start, $length, 'UTF-8');
    }

    /**
     * Make a string's first character to upper case.
     * @param string $value
     * @return string
     */
    public static function ucfirst(string $value): string
    {
        return static::upper(
            static::substr($value, 0, 1)
        ) . static::substr($value, 1);
    }

    /**
     * Split the string by length part
     * @param string $value
     * @param int $length
     * @return array<int, string>
     */
    public static function split(string $value, int $length = 1): array
    {
        if ($length < 1) {
            return [];
        }

        if (self::isAscii($value)) {
            $res = str_split($value, $length);
            if ($res === false) {
                return [];
            }

            return $res;
        }

        if (mb_strlen($value) <= $length) {
            return [$value];
        }
        $matches = [];
        preg_match_all(
            '/.{' . $length . '}|[^\x00]{1,' . $length . '}$/us',
            $value,
            $matches
        );

        return $matches[0];
    }

    /**
     * Check whether the given string contains only ASCII chars
     * @param string $value
     * @return bool
     */
    public static function isAscii(string $value): bool
    {
        return (bool)!preg_match('/[^\x00-\x7F]/S', $value);
    }

    /**
     * Put string to lower case
     * @param string $value
     * @return string
     */
    public static function lower(string $value): string
    {
        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * Put string to upper case
     * @param string $value
     * @return string
     */
    public static function upper(string $value): string
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    /**
     * Return the unique ID
     * @param int $length
     *
     * @return string
     */
    public static function uniqId(int $length = 13): string
    {
        $bytes = random_bytes((int) ceil($length / 2));

        return (string)substr(bin2hex($bytes), 0, $length);
    }

    /**
     * Put array to HTML attributes
     * @param array<string, mixed> $attributes
     * @return string
     */
    public static function toAttribute(array $attributes): string
    {
        if (empty($attributes)) {
            return '';
        }

        // handle boolean, array, & html special chars
        array_walk($attributes, function (&$value, $key) {
            $value = is_bool($value) ? $value ? 'true' : 'false' : $value;
            $value = is_array($value) ? implode(' ', $value) : $value;
            $value = trim($value);
            $value = htmlspecialchars($value);
        });

        // remove empty elements
        $emptyAttributes = array_filter($attributes, function ($value) {
            return strlen($value) > 0;
        });

        if (empty($emptyAttributes)) {
            return '';
        }

        $compiled = implode('="%s" ', array_keys($emptyAttributes)) . '="%s"';

        return vsprintf($compiled, array_values($emptyAttributes));
    }

    /**
     * Generate random string value
     * @param int $length
     * @return string
     */
    public static function random(int $length = 16): string
    {
        $string = '';
        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $bytes = random_bytes($size);

            $string .= substr(
                str_replace(['/', '+', '='], '', base64_encode($bytes)),
                0,
                $size
            );
        }

        return $string;
    }

    /**
     * Generates a random string of a given type and length. Possible
     * values for the first argument ($type) are:
     *  - alnum    - alpha-numeric characters (including capitals)
     *  - alpha    - alphabetical characters (including capitals)
     *  - hexdec   - hexadecimal characters, 0-9 plus a-f
     *  - numeric  - digit characters, 0-9
     *  - nozero   - digit characters, 1-9
     *  - distinct - clearly distinct alpha-numeric characters.
     * @param string $type
     * @param int $length
     * @return string
     */
    public static function randomString(string $type = 'alnum', int $length = 8): string
    {
        $utf8 = false;

        switch ($type) {
            case 'alnum':
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alpha':
                $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'lowalnum':
                $pool = '0123456789abcdefghijklmnopqrstuvwxyz';
                break;
            case 'hexdec':
                $pool = '0123456789abcdef';
                break;
            case 'numeric':
                $pool = '0123456789';
                break;
            case 'nozero':
                $pool = '123456789';
                break;
            case 'distinct':
                $pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
                break;
            default:
                $pool = (string)$type;
                $utf8 = !self::isAscii($pool);
                break;
        }

        // Split the pool into an array of characters
        $pool = $utf8 ? self::split($pool, 1) : str_split($pool, 1);
        // Largest pool key
        $max = count($pool) - 1;

        $str = '';
        for ($i = 0; $i < $length; $i++) {
            // Select a random character from the pool and add it to the string
            $str .= $pool[random_int(0, $max)];
        }

        // Make sure alnum strings contain at least one letter and one digit
        if ($type === 'alnum' && $length > 1) {
            if (ctype_alpha($str)) {
                // Add a random digit
                $str[random_int(0, $length - 1)] = chr(random_int(48, 57));
            } elseif (ctype_digit($str)) {
                // Add a random letter
                $str[random_int(0, $length - 1)] = chr(random_int(65, 90));
            }
        }

        return $str;
    }

    /**
     * Create a simple random token-string
     * @param int $length
     * @param string $salt
     * @return string
     */
    public static function randomToken(int $length = 24, string $salt = ''): string
    {
        $string = '';
        $chars  = '0456789abc1def2ghi3jkl';
        $maxVal = strlen($chars) - 1;

        for ($i = 0; $i < $length; ++$i) {
            $string .= $chars[random_int(0, $maxVal)];
        }

        return md5($string . $salt);
    }

    /**
     * Convert the given value to string representation
     * @param mixed $value
     * @return string
     */
    public static function stringify($value): string
    {
        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        if (is_array($value)) {
            return self::stringifyArray($value);
        }

        if (is_object($value)) {
            return self::stringifyObject($value);
        }

        if (is_resource($value)) {
            return sprintf('resource<%s>', get_resource_type($value));
        }

        return gettype($value);
    }

    /**
     * Convert the given array to string representation
     * @param array<mixed> $value
     * @return string
     */
    public static function stringifyArray(array $value): string
    {
        if (empty($value)) {
            return '[]';
        }

        $keys = array_keys($value);
        $values = array_values($value);
        [$firstKey] = $keys;
        $ignoreKeys = $firstKey === 0;

        return sprintf('[%s]', implode(', ', array_map(
            function ($key, $value) use ($ignoreKeys) {
                return $ignoreKeys
                        ? self::stringify($value)
                        : sprintf(
                            '%s => %s',
                            self::stringify($key),
                            self::stringify($value)
                        );
            },
            $keys,
            $values
        )));
    }

    /**
     * Convert the given object to string representation
     * @param object $value
     * @return string
     */
    public static function stringifyObject(object $value): string
    {
        $valueClass = get_class($value);

        if ($value instanceof Throwable) {
            return sprintf(
                '%s { "%s", %s, %s #%s }',
                $valueClass,
                $value->getMessage(),
                $value->getCode(),
                $value->getFile(),
                $value->getLine()
            );
        }

        if (method_exists($value, '__toString')) {
            return sprintf('%s { %s }', $valueClass, $value->__toString());
        }

        if (method_exists($value, 'toString')) {
            return sprintf('%s { %s }', $valueClass, $value->toString());
        }

        if ($value instanceof Traversable) {
            return sprintf(
                '%s %s',
                $valueClass,
                self::stringifyArray(iterator_to_array($value))
            );
        }

        if ($value instanceof DateTimeInterface) {
            return sprintf(
                '%s { %s }',
                $valueClass,
                $value->format(DateTime::ATOM)
            );
        }

        if ($value instanceof JsonSerializable) {
            return sprintf(
                '%s {%s}',
                $valueClass,
                trim((string) json_encode($value->jsonSerialize()), '{}')
            );
        }

        return $valueClass;
    }

    /**
     * Return the user ip address
     * @return string
     */
    public static function ip(): string
    {
        $ip = '127.0.0.1';

        $ipServerVars = [
            'REMOTE_ADDR',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED'
        ];

        foreach ($ipServerVars as $var) {
            //https://bugs.php.net/bug.php?id=49184 can
            // not use filter_input(INPUT_SERVER, $var);

            if (isset($_SERVER[$var])) {
                $ip = htmlspecialchars(
                    strip_tags((string) $_SERVER[$var]),
                    ENT_COMPAT,
                    'UTF-8'
                );
                break;
            }
        }

        // Strip any secondary IP etc from the IP address
        if (strpos($ip, ',') > 0) {
            $ip = substr($ip, 0, strpos($ip, ','));
        }

        return $ip;
    }

    /**
     * Return the ASCII replacement
     * @return array<string, array<string>>
     */
    private static function getChars(): array
    {
        return [
            '0'    => ['°', '₀'],
            '1'    => ['¹', '₁'],
            '2'    => ['²', '₂'],
            '3'    => ['³', '₃'],
            '4'    => ['⁴', '₄'],
            '5'    => ['⁵', '₅'],
            '6'    => ['⁶', '₆'],
            '7'    => ['⁷', '₇'],
            '8'    => ['⁸', '₈'],
            '9'    => ['⁹', '₉'],
            'a'    => [
                'à',
                'á',
                'ả',
                'ã',
                'ạ',
                'ă',
                'ắ',
                'ằ',
                'ẳ',
                'ẵ',
                'ặ',
                'â',
                'ấ',
                'ầ',
                'ẩ',
                'ẫ',
                'ậ',
                'ā',
                'ą',
                'å',
                'α',
                'ά',
                'ἀ',
                'ἁ',
                'ἂ',
                'ἃ',
                'ἄ',
                'ἅ',
                'ἆ',
                'ἇ',
                'ᾀ',
                'ᾁ',
                'ᾂ',
                'ᾃ',
                'ᾄ',
                'ᾅ',
                'ᾆ',
                'ᾇ',
                'ὰ',
                'ά',
                'ᾰ',
                'ᾱ',
                'ᾲ',
                'ᾳ',
                'ᾴ',
                'ᾶ',
                'ᾷ',
                'а',
                'أ',
                'အ',
                'ာ',
                'ါ',
                'ǻ',
                'ǎ',
                'ª',
                'ა',
                'अ'
            ],
            'b'    => ['б', 'β', 'Ъ', 'Ь', 'ب', 'ဗ', 'ბ'],
            'c'    => ['ç', 'ć', 'č', 'ĉ', 'ċ'],
            'd'    => ['ď', 'ð', 'đ', 'ƌ', 'ȡ', 'ɖ', 'ɗ', 'ᵭ', 'ᶁ', 'ᶑ', 'д', 'δ', 'د', 'ض', 'ဍ', 'ဒ', 'დ'],
            'e'    => [
                'é',
                'è',
                'ẻ',
                'ẽ',
                'ẹ',
                'ê',
                'ế',
                'ề',
                'ể',
                'ễ',
                'ệ',
                'ë',
                'ē',
                'ę',
                'ě',
                'ĕ',
                'ė',
                'ε',
                'έ',
                'ἐ',
                'ἑ',
                'ἒ',
                'ἓ',
                'ἔ',
                'ἕ',
                'ὲ',
                'έ',
                'е',
                'ё',
                'э',
                'є',
                'ə',
                'ဧ',
                'ေ',
                'ဲ',
                'ე',
                'ए'
            ],
            'f'    => ['ф', 'φ', 'ف', 'ƒ', 'ფ'],
            'g'    => ['ĝ', 'ğ', 'ġ', 'ģ', 'г', 'ґ', 'γ', 'ج', 'ဂ', 'გ'],
            'h'    => ['ĥ', 'ħ', 'η', 'ή', 'ح', 'ه', 'ဟ', 'ှ', 'ჰ'],
            'i'    => [
                'í',
                'ì',
                'ỉ',
                'ĩ',
                'ị',
                'î',
                'ï',
                'ī',
                'ĭ',
                'į',
                'ı',
                'ι',
                'ί',
                'ϊ',
                'ΐ',
                'ἰ',
                'ἱ',
                'ἲ',
                'ἳ',
                'ἴ',
                'ἵ',
                'ἶ',
                'ἷ',
                'ὶ',
                'ί',
                'ῐ',
                'ῑ',
                'ῒ',
                'ΐ',
                'ῖ',
                'ῗ',
                'і',
                'ї',
                'и',
                'ဣ',
                'ိ',
                'ီ',
                'ည်',
                'ǐ',
                'ი',
                'इ'
            ],
            'j'    => ['ĵ', 'ј', 'Ј', 'ჯ'],
            'k'    => ['ķ', 'ĸ', 'к', 'κ', 'Ķ', 'ق', 'ك', 'က', 'კ', 'ქ'],
            'l'    => ['ł', 'ľ', 'ĺ', 'ļ', 'ŀ', 'л', 'λ', 'ل', 'လ', 'ლ'],
            'm'    => ['м', 'μ', 'م', 'မ', 'მ'],
            'n'    => ['ñ', 'ń', 'ň', 'ņ', 'ŉ', 'ŋ', 'ν', 'н', 'ن', 'န', 'ნ'],
            'o'    => [
                'ó',
                'ò',
                'ỏ',
                'õ',
                'ọ',
                'ô',
                'ố',
                'ồ',
                'ổ',
                'ỗ',
                'ộ',
                'ơ',
                'ớ',
                'ờ',
                'ở',
                'ỡ',
                'ợ',
                'ø',
                'ō',
                'ő',
                'ŏ',
                'ο',
                'ὀ',
                'ὁ',
                'ὂ',
                'ὃ',
                'ὄ',
                'ὅ',
                'ὸ',
                'ό',
                'о',
                'و',
                'θ',
                'ို',
                'ǒ',
                'ǿ',
                'º',
                'ო',
                'ओ'
            ],
            'p'    => ['п', 'π', 'ပ', 'პ'],
            'q'    => ['ყ'],
            'r'    => ['ŕ', 'ř', 'ŗ', 'р', 'ρ', 'ر', 'რ'],
            's'    => ['ś', 'š', 'ş', 'с', 'σ', 'ș', 'ς', 'س', 'ص', 'စ', 'ſ', 'ს'],
            't'    => ['ť', 'ţ', 'т', 'τ', 'ț', 'ت', 'ط', 'ဋ', 'တ', 'ŧ', 'თ', 'ტ'],
            'u'    => [
                'ú',
                'ù',
                'ủ',
                'ũ',
                'ụ',
                'ư',
                'ứ',
                'ừ',
                'ử',
                'ữ',
                'ự',
                'û',
                'ū',
                'ů',
                'ű',
                'ŭ',
                'ų',
                'µ',
                'у',
                'ဉ',
                'ု',
                'ူ',
                'ǔ',
                'ǖ',
                'ǘ',
                'ǚ',
                'ǜ',
                'უ',
                'उ'
            ],
            'v'    => ['в', 'ვ', 'ϐ'],
            'w'    => ['ŵ', 'ω', 'ώ', 'ဝ', 'ွ'],
            'x'    => ['χ', 'ξ'],
            'y'    => ['ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ', 'ÿ', 'ŷ', 'й', 'ы', 'υ', 'ϋ', 'ύ', 'ΰ', 'ي', 'ယ'],
            'z'    => ['ź', 'ž', 'ż', 'з', 'ζ', 'ز', 'ဇ', 'ზ'],
            'aa'   => ['ع', 'आ'],
            'ae'   => ['ä', 'æ', 'ǽ'],
            'ai'   => ['ऐ'],
            'at'   => ['@'],
            'ch'   => ['ч', 'ჩ', 'ჭ'],
            'dj'   => ['ђ', 'đ'],
            'dz'   => ['џ', 'ძ'],
            'ei'   => ['ऍ'],
            'gh'   => ['غ', 'ღ'],
            'ii'   => ['ई'],
            'ij'   => ['ĳ'],
            'kh'   => ['х', 'خ', 'ხ'],
            'lj'   => ['љ'],
            'nj'   => ['њ'],
            'oe'   => ['ö', 'œ'],
            'oi'   => ['ऑ'],
            'oii'  => ['ऒ'],
            'ps'   => ['ψ'],
            'sh'   => ['ш', 'შ'],
            'shch' => ['щ'],
            'ss'   => ['ß'],
            'sx'   => ['ŝ'],
            'th'   => ['þ', 'ϑ', 'ث', 'ذ', 'ظ'],
            'ts'   => ['ц', 'ც', 'წ'],
            'ue'   => ['ü'],
            'uu'   => ['ऊ'],
            'ya'   => ['я'],
            'yu'   => ['ю'],
            'zh'   => ['ж', 'ჟ'],
            '(c)'  => ['©'],
            'A'    => [
                'Á',
                'À',
                'Ả',
                'Ã',
                'Ạ',
                'Ă',
                'Ắ',
                'Ằ',
                'Ẳ',
                'Ẵ',
                'Ặ',
                'Â',
                'Ấ',
                'Ầ',
                'Ẩ',
                'Ẫ',
                'Ậ',
                'Å',
                'Ā',
                'Ą',
                'Α',
                'Ά',
                'Ἀ',
                'Ἁ',
                'Ἂ',
                'Ἃ',
                'Ἄ',
                'Ἅ',
                'Ἆ',
                'Ἇ',
                'ᾈ',
                'ᾉ',
                'ᾊ',
                'ᾋ',
                'ᾌ',
                'ᾍ',
                'ᾎ',
                'ᾏ',
                'Ᾰ',
                'Ᾱ',
                'Ὰ',
                'Ά',
                'ᾼ',
                'А',
                'Ǻ',
                'Ǎ'
            ],
            'B'    => ['Б', 'Β', 'ब'],
            'C'    => ['Ç', 'Ć', 'Č', 'Ĉ', 'Ċ'],
            'D'    => ['Ď', 'Ð', 'Đ', 'Ɖ', 'Ɗ', 'Ƌ', 'ᴅ', 'ᴆ', 'Д', 'Δ'],
            'E'    => [
                'É',
                'È',
                'Ẻ',
                'Ẽ',
                'Ẹ',
                'Ê',
                'Ế',
                'Ề',
                'Ể',
                'Ễ',
                'Ệ',
                'Ë',
                'Ē',
                'Ę',
                'Ě',
                'Ĕ',
                'Ė',
                'Ε',
                'Έ',
                'Ἐ',
                'Ἑ',
                'Ἒ',
                'Ἓ',
                'Ἔ',
                'Ἕ',
                'Έ',
                'Ὲ',
                'Е',
                'Ё',
                'Э',
                'Є',
                'Ə'
            ],
            'F'    => ['Ф', 'Φ'],
            'G'    => ['Ğ', 'Ġ', 'Ģ', 'Г', 'Ґ', 'Γ'],
            'H'    => ['Η', 'Ή', 'Ħ'],
            'I'    => [
                'Í',
                'Ì',
                'Ỉ',
                'Ĩ',
                'Ị',
                'Î',
                'Ï',
                'Ī',
                'Ĭ',
                'Į',
                'İ',
                'Ι',
                'Ί',
                'Ϊ',
                'Ἰ',
                'Ἱ',
                'Ἳ',
                'Ἴ',
                'Ἵ',
                'Ἶ',
                'Ἷ',
                'Ῐ',
                'Ῑ',
                'Ὶ',
                'Ί',
                'И',
                'І',
                'Ї',
                'Ǐ',
                'ϒ'
            ],
            'K'    => ['К', 'Κ'],
            'L'    => ['Ĺ', 'Ł', 'Л', 'Λ', 'Ļ', 'Ľ', 'Ŀ', 'ल'],
            'M'    => ['М', 'Μ'],
            'N'    => ['Ń', 'Ñ', 'Ň', 'Ņ', 'Ŋ', 'Н', 'Ν'],
            'O'    => [
                'Ó',
                'Ò',
                'Ỏ',
                'Õ',
                'Ọ',
                'Ô',
                'Ố',
                'Ồ',
                'Ổ',
                'Ỗ',
                'Ộ',
                'Ơ',
                'Ớ',
                'Ờ',
                'Ở',
                'Ỡ',
                'Ợ',
                'Ø',
                'Ō',
                'Ő',
                'Ŏ',
                'Ο',
                'Ό',
                'Ὀ',
                'Ὁ',
                'Ὂ',
                'Ὃ',
                'Ὄ',
                'Ὅ',
                'Ὸ',
                'Ό',
                'О',
                'Θ',
                'Ө',
                'Ǒ',
                'Ǿ'
            ],
            'P'    => ['П', 'Π'],
            'R'    => ['Ř', 'Ŕ', 'Р', 'Ρ', 'Ŗ'],
            'S'    => ['Ş', 'Ŝ', 'Ș', 'Š', 'Ś', 'С', 'Σ'],
            'T'    => ['Ť', 'Ţ', 'Ŧ', 'Ț', 'Т', 'Τ'],
            'U'    => [
                'Ú',
                'Ù',
                'Ủ',
                'Ũ',
                'Ụ',
                'Ư',
                'Ứ',
                'Ừ',
                'Ử',
                'Ữ',
                'Ự',
                'Û',
                'Ū',
                'Ů',
                'Ű',
                'Ŭ',
                'Ų',
                'У',
                'Ǔ',
                'Ǖ',
                'Ǘ',
                'Ǚ',
                'Ǜ'
            ],
            'V'    => ['В'],
            'W'    => ['Ω', 'Ώ', 'Ŵ'],
            'X'    => ['Χ', 'Ξ'],
            'Y'    => ['Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ', 'Ÿ', 'Ῠ', 'Ῡ', 'Ὺ', 'Ύ', 'Ы', 'Й', 'Υ', 'Ϋ', 'Ŷ'],
            'Z'    => ['Ź', 'Ž', 'Ż', 'З', 'Ζ'],
            'AE'   => ['Ä', 'Æ', 'Ǽ'],
            'CH'   => ['Ч'],
            'DJ'   => ['Ђ'],
            'DZ'   => ['Џ'],
            'GX'   => ['Ĝ'],
            'HX'   => ['Ĥ'],
            'IJ'   => ['Ĳ'],
            'JX'   => ['Ĵ'],
            'KH'   => ['Х'],
            'LJ'   => ['Љ'],
            'NJ'   => ['Њ'],
            'OE'   => ['Ö', 'Œ'],
            'PS'   => ['Ψ'],
            'SH'   => ['Ш'],
            'SHCH' => ['Щ'],
            'SS'   => ['ẞ'],
            'TH'   => ['Þ'],
            'TS'   => ['Ц'],
            'UE'   => ['Ü'],
            'YA'   => ['Я'],
            'YU'   => ['Ю'],
            'ZH'   => ['Ж'],
            ' '    => [
                "\xC2\xA0",
                "\xE2\x80\x80",
                "\xE2\x80\x81",
                "\xE2\x80\x82",
                "\xE2\x80\x83",
                "\xE2\x80\x84",
                "\xE2\x80\x85",
                "\xE2\x80\x86",
                "\xE2\x80\x87",
                "\xE2\x80\x88",
                "\xE2\x80\x89",
                "\xE2\x80\x8A",
                "\xE2\x80\xAF",
                "\xE2\x81\x9F",
                "\xE3\x80\x80"
            ],
        ];
    }
}
