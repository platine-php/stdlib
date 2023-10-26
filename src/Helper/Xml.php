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
 *  @file Xml.php
 *
 *  The XML helper class
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
 * Class Xml
 * @package Platine\Stdlib\Helper
 */
class Xml
{
    /**
     * Transform an XML string to array
     * @param string $xml
     * @return array<string, mixed>
     */
    public static function decode(string $xml): array
    {
        return self::xmlToArray($xml);
    }

    /**
     * Transform an array to XML
     * @param array<int|string, mixed|iterable> $data
     * @return string
     */
    public static function encode($data): string
    {
        $xml = '<xml>';
        $xml .= self::arrayToXml($data);
        $xml .= '</xml>';

        return $xml;
    }

    /**
     * Transform an XML string to array
     * @param string $xml
     * @return array<string, mixed>
     */
    public static function xmlToArray(string $xml): array
    {
        $string = simplexml_load_string(
            $xml,
            'SimpleXMLElement',
            LIBXML_NOCDATA | LIBXML_NOBLANKS
        );

        $jsonString = Json::encode($string);

        /** @var array<mixed> $data */
        $data = Json::decode($jsonString, true);

        return $data;
    }

    /**
     * Transform an array to XML
     * @param array<int|string, mixed|iterable> $data
     * @return string
     */
    public static function arrayToXml($data): string
    {
        $xml = '';
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (is_int($key) === false) {
                    $xml .= '<' . $key . '>';
                }
                if (is_iterable($value)) {
                    /** @var array<string, mixed|iterable> $value */
                    $xml .= self::arrayToXml($value);
                } elseif (is_numeric($value)) {
                    $xml .= $value;
                } else {
                    $xml .= self::addCharacterData($value);
                }
                if (is_int($key) === false) {
                    $xml .= '</' . $key . '>';
                }
            }
        }

        return $xml;
    }

    /**
     * Add CDATA to the given string
     * @param string $value
     * @return string
     */
    protected static function addCharacterData(string $value): string
    {
        return sprintf('<![CDATA[%s]]>', $value);
    }
}
