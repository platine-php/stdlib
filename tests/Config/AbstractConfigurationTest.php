<?php

declare(strict_types=1);

namespace Platine\Test\Stdlib\Config;

use Error;
use InvalidArgumentException;
use Platine\Dev\PlatineTestCase;
use Platine\Stdlib\Config\AbstractConfiguration;
use Platine\Test\Fixture\Stdlib\MyTestAppConfiguration;
use stdClass;

/**
 * AbstractConfiguration class tests
 *
 * @group core
 * @group configuration
 */
class AbstractConfigurationTest extends PlatineTestCase
{


    public function testConstructorDefault(): void
    {
        $cfg = new MyTestAppConfiguration([]);
        $this->assertInstanceOf(AbstractConfiguration::class, $cfg);
    }

    public function testConstructorWithConfig(): void
    {
        $cfg = new MyTestAppConfiguration([
            'a_int' => 10,
            'b_bool' => true,
            'c_obj' => new stdClass(),
            'd_arr' => [1, 2, 3],
        ]);

        $this->assertEquals(10, $cfg->get('a_int'));
        $this->assertEquals(true, $cfg->get('b_bool'));
        $this->assertInstanceOf(stdClass::class, $cfg->get('c_obj'));
        $this->assertEquals([1, 2, 3], $cfg->get('d_arr'));
    }

    public function testGetConfigNotFound(): void
    {
        $cfg = new MyTestAppConfiguration([

        ]);
        $this->expectException(InvalidArgumentException::class);
        $cfg->get('a_int');
    }

    public function testSetConfigInvalidValueScalar(): void
    {
        $this->expectException(Error::class);
        $cfg = new MyTestAppConfiguration([
            'a_int' => 10.8
        ]);
    }

    public function testSetConfigInvalidValueObject(): void
    {
        $this->expectException(Error::class);
        $cfg = new MyTestAppConfiguration([
            'c_obj' => 123,
        ]);
    }

    public function testGetter(): void
    {
        $cfg = new class extends AbstractConfiguration{

        };

        $this->assertEmpty($cfg->getSetterMaps());
        $this->assertEmpty($cfg->getValidationRules());
    }
}
