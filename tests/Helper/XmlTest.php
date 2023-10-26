<?php

declare(strict_types=1);

namespace Platine\Test\Stdlib\Helper;

use ArrayIterator;
use Platine\Dev\PlatineTestCase;
use Platine\Stdlib\Helper\Xml;

/**
 * Xml class tests
 *
 * @group core
 * @group helpers
 */
class XmlTest extends PlatineTestCase
{
    /**
     * @dataProvider commonDataProvider
     * @param string $method method to call
     * @param array<int, mixed> $args methods arguments
     * @param array<int, string> $globalVarsMock variables name for global mock
     * @param mixed $expected
     * @return void
     */
    public function testCommonMethods(
        string $method,
        array $args,
        array $globalVarsMock,
        $expected
    ): void {
        foreach ($globalVarsMock as $var) {
            global $$var;
        }

        foreach ($globalVarsMock as $var) {
            $$var = true;
        }

        $this->assertEquals($expected, Xml::$method(...$args));
    }



    /**
     * Data provider for "testCommonMethods"
     * @return array
     */
    public function commonDataProvider(): array
    {
        return [
          [
              'decode',
              ['<xml><id>1</id><name>foo</name></xml>'],
              [],
              ['id' => 1, 'name' => 'foo']
          ],
          [
              'encode',
              [['id' => 1, 'name' => 'foo']],
              [],
              '<xml><id>1</id><name><![CDATA[foo]]></name></xml>'
          ],
           [
              'encode',
              [[['data' => ['id' => 1, 'name' => 'foo']], ['data' => ['id' => 2, 'name' => 'bar']]]],
              [],
              '<xml><data><id>1</id><name><![CDATA[foo]]></name></data><data><id>2</id><name><![CDATA[bar]]></name></data></xml>'
          ],
          [
              'encode',
              [new ArrayIterator(['id' => 1, 'name' => 'foo'])],
              [],
              '<xml><id>1</id><name><![CDATA[foo]]></name></xml>'
          ],
          [
              'encode',
              [['users' => new ArrayIterator(['id' => 1, 'name' => 'foo'])]],
              [],
              '<xml><users><id>1</id><name><![CDATA[foo]]></name></users></xml>'
          ],

        ];
    }
}
