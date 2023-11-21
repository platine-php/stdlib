<?php

declare(strict_types=1);

namespace Platine\Test\Fixture\Stdlib;

use Composer\Autoload\ClassLoader;
use JsonSerializable;
use Platine\Stdlib\Config\AbstractConfiguration;
use stdClass;

class ComposerAutoloadObject extends ClassLoader
{
    public function __construct()
    {
    }
}

class phpCallClassMethodCallback
{
    public function foo($a)
    {
        return 'bar' . $a;
    }
}

class Stringify__toString
{
    public function __toString(): string
    {
        return __CLASS__;
    }
}

class StringifytoString
{
    public function toString(): string
    {
        return __CLASS__;
    }
}

class StringifyJson implements JsonSerializable
{
    public function jsonSerialize()
    {
        return [1, 2, 3];
    }
}

class phpCallClassCommon
{
    public function foo($a)
    {
        function jsonSerialize(): mixed
        {
        }

        return 'bar' . $a;
    }

    public static function bar($a)
    {
        return 'foo' . $a;
    }
}

class phpCallClassInvokeCallback
{
    public function __invoke($a)
    {
        return 'bar' . $a;
    }
}

class MyTestAppConfiguration extends AbstractConfiguration
{
    protected int $aInt = 0;
    protected bool $bBool = false;
    protected ?bool $bBoolNull = null;
    protected ?stdClass $cObj = null;
    protected array $dArr = [];

    public function getValidationRules(): array
    {
        return [
            'a_int' => 'integer',
            'b_bool' => 'boolean',
            'b_bool_null' => 'boolean',
            'c_obj' => 'object::' . (stdClass::class),
            'd_arr' => 'array',
            'd_arr.foo.bar' => 'integer',
            'd_arr.state' => 'boolean',
            'not_found_key' => 'boolean',
        ];
    }

    public function setAint(int $a)
    {
        $this->aInt = $a;
    }

    public function getSetterMaps(): array
    {
        return [
           'dArr' => 'setArray'
        ];
    }

    public function getDefault(): array
    {
        return [
           'a_int' => 100
        ];
    }

    public function setArray(array $val): self
    {
        $this->dArr = $val;

        return $this;
    }
}

function phpCallCallbackSimpleFunction()
{
    return 1;
}
