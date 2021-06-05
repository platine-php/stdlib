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
 *  @file Composer.php
 *
 *  The Composer helper class
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

use Composer\Autoload\ClassLoader;
use RuntimeException;

/**
 * Class Composer
 * @package Platine\Stdlib\Helper
 */
class Composer
{

    /**
     * The composer class loader instance
     * @var ClassLoader|null
     */
    protected static ?ClassLoader $classLoader = null;


    /**
     * Return the composer class loader instance
     * @return ClassLoader
     * @throws RuntimeException
     */
    public static function getClasstLoader(): ClassLoader
    {
        if (self::$classLoader) {
            return self::$classLoader;
        }

        $autoloadFunctions = (array)spl_autoload_functions();
        foreach ($autoloadFunctions as $loader) {
            if (is_array($loader) && isset($loader[0])) {
                $composerLoader = $loader[0];

                if (
                    is_object($composerLoader)
                        && $composerLoader instanceof ClassLoader
                ) {
                    self::$classLoader = $composerLoader;

                    return self::$classLoader;
                }
            }
        }

        throw new RuntimeException('Composer class loader not found');
    }

    /**
     * Parse composer lock file and return the packages information
     * @param string $path
     * @param callable|null $filter
     *
     * @return array<mixed>
     */
    public static function parseLockFile(string $path, ?callable $filter = null): array
    {
        $filename = Path::normalizePathDS($path, true) . 'composer.lock';

        if (!file_exists($filename)) {
            throw new RuntimeException(sprintf(
                'Composer lock file [%s] does not exists',
                $filename
            ));
        }


        $json = file_get_contents($filename);

        if (!$json) {
            return [];
        }

        /** @var array<mixed> $data */
        $data = json_decode($json, true);
        if (empty($data) || !isset($data['packages'])) {
            return [];
        }

        $packages = [];
        foreach ($data['packages'] as $pkg) {
            if ($filter && $filter($pkg['name'], $pkg['type']) === false) {
                continue;
            }

            $packages[] = $pkg;
        }

        return $packages;
    }
}
