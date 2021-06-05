<?php

declare(strict_types=1);

namespace Platine\Test\Stdlib\Helper;

use InvalidArgumentException;
use Platine\PlatineTestCase;
use Platine\Stdlib\Helper\Json;
use stdClass;

/**
 * Json class tests
 *
 * @group core
 * @group helpers
 */
class JsonTest extends PlatineTestCase
{
    public function testDecodeError(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Json::decode('rt');
    }

    public function testDecodeArray(): void
    {
        $res = Json::decode('{"a":3, "b": 4}', true);

        $this->assertIsArray($res);
        $this->assertCount(2, $res);
        $this->assertArrayHasKey('a', $res);
        $this->assertArrayHasKey('b', $res);
        $this->assertEquals(4, $res['b']);
        $this->assertEquals(3, $res['a']);
    }

    public function testDecodeObject(): void
    {
        $res = Json::decode('{"a":3, "b": 4}');

        $this->assertIsObject($res);
        $this->assertInstanceOf(stdClass::class, $res);
        $this->assertObjectHasAttribute('a', $res);
        $this->assertObjectHasAttribute('b', $res);
        $this->assertEquals(4, $res->b);
        $this->assertEquals(3, $res->a);
    }

    public function testEncodeError(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Json::encode(urldecode('bad utf string %C4_'));
    }

    public function testEncodeSuccess(): void
    {
        $this->assertEquals(Json::encode(['a' => 6, 'b' => 7]), '{"a":6,"b":7}');
    }
}
