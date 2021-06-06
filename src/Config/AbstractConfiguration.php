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
 *  @file AbstractConfiguration.php
 *
 *  The base class for application
 *
 *  @package    Platine\Stdlib\Config
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Stdlib\Config;

use Error;
use InvalidArgumentException;
use Platine\Stdlib\Contract\ConfigurationInterface;
use Platine\Stdlib\Helper\Arr;
use Platine\Stdlib\Helper\Str;

/**
 * Class AbstractConfiguration
 * @package Platine\Stdlib\Config
 */
abstract class AbstractConfiguration implements ConfigurationInterface
{

    /**
     * The raw configuration array
     * @var array<string, mixed>
     */
    protected array $config = [];

    /**
     * {@inheritedoc}
     */
    public function __construct(array $config = [])
    {
        $this->load($config);
    }

    /**
     * {@inheritedoc}
     */
    public function get(string $name)
    {
        if (!Arr::has($this->config, $name)) {
            throw new InvalidArgumentException(sprintf(
                'Configuration [%s] does not exist',
                $name
            ));
        }

        return Arr::get($this->config, $name);
    }

    /**
     * {@inheritedoc}
     */
    public function load(array $config): void
    {
        $this->config = $config;
        $rules = $this->getValidationRules();
        $setters = $this->getSetterMaps();

        foreach ($config as $name => $value) {
            $key = Str::camel($name, true);
            if (property_exists($this, $key)) {
                $this->checkValue($key, $value, $rules);

                if (Arr::has($setters, $key)) {
                    $method = Arr::get($setters, $key);
                    $this->{$method}($value);
                } else {
                    $this->{$key} = $value;
                }
            }
        }
    }

    /**
     * {@inheritedoc}
     */
    public function getValidationRules(): array
    {
        return [];
    }

    /**
     * {@inheritedoc}
     */
    public function getSetterMaps(): array
    {
        return [];
    }

    /**
     * Check the configuration for the given type
     * @param string $key
     * @param mixed $value
     * @param array<string, string> $rules
     * @return void
     */
    private function checkValue(string $key, $value, array $rules = []): void
    {
        if (array_key_exists($key, $rules)) {
            $expectedType = $rules[$key];
            $type = gettype($value);
            $className = null;
            if (strpos($expectedType, 'object::') === 0) {
                $className = substr($expectedType, 8);
            }

            $error = null;

            if ($className !== null) {
                if (!($value instanceof $className)) {
                    $error = 'Invalid configuration [%s] instance value, expected [%s], but got [%s]';
                }
            } elseif ($type !== $expectedType) {
                $error = 'Invalid configuration [%s] value, expected [%s], but got [%s]';
            }

            if ($error !== null) {
                throw new Error(sprintf(
                    $error,
                    Str::snake($key),
                    $className ?? $expectedType,
                    is_object($value) ? get_class($value) : gettype($value)
                ));
            }
        }
    }
}
