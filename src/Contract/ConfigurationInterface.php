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
 *  @file ConfigurationInterface.php
 *
 *  The interface for all classes that can be use as application configuration
 *
 *  @package    Platine\Stdlib\Contract
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Stdlib\Contract;

/**
 * Class ConfigurationInterface
 * @package Platine\Stdlib\Contract
 */
interface ConfigurationInterface
{

    /**
     * Create new instance
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = []);

    /**
     * Return the value of the given configuration
     * @param string $name
     * @return mixed
     */
    public function get(string $name);

    /**
     * Load the configuration
     * @param array<string, mixed> $config
     * @return void
     */
    public function load(array $config): void;

    /**
     * Return the validation rules
     * with format of:
     * - property key => type (result of gettype())
     * - For instance use format of object::FullClassName
     * @return array<string, string>
     */
    public function getValidationRules(): array;

    /**
     * Return the setters maps
     * with format of:
     * - property key => method name
     * @return array<string, string>
     */
    public function getSetterMaps(): array;
}
