<?php

declare(strict_types=1);

namespace Platine\Test\Fixture\Stdlib;

use Composer\Autoload\ClassLoader;
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

class phpCallClassCommon
{
    public function foo($a)
    {
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
    protected ?stdClass $cObj = null;
    protected array $dArr = [];

    public function getValidationRules(): array
    {
        return [
            'aInt' => 'integer',
            'bBool' => 'boolean',
            'cObj' => 'object::' . (stdClass::class),
            'dArr' => 'array',
        ];
    }
}

function phpCallCallbackSimpleFunction()
{
    return 1;
}
