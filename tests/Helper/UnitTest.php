<?php

declare(strict_types=1);

namespace Platine\Test\Stdlib\Helper;

use Platine\PlatineTestCase;
use Platine\Stdlib\Helper\Unit;

/**
 * Unit class tests
 *
 * @group core
 * @group helpers
 */
class UnitTest extends PlatineTestCase
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

        $this->assertEquals($expected, Unit::$method(...$args));
    }



    /**
     * Data provider for "testCommonMethods"
     * @return array
     */
    public function commonDataProvider(): array
    {
        return [
          [
              'sizeInBytes',
              ['2M'],
              [],
              2097152
          ],
          [
              'sizeInBytes',
              ['2.89T'],
              [],
              3177588604272
          ],
          [
              'sizeInBytes',
              ['1.8g'],
              [],
              1932735283
          ],
          [
              'formatSize',
              [1932135283, 4],
              [],
              '1.7994G'
          ],
          [
              'formatSize',
              [0, 4],
              [],
              ''
          ],
          [
              'formatSize',
              [-1, 4],
              [],
              ''
          ],

        ];
    }
}
