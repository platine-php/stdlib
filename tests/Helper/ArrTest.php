<?php

declare(strict_types=1);

namespace Platine\Test\Stdlib\Helper;

use Platine\PlatineTestCase;
use Platine\Stdlib\Helper\Arr;

/**
 * Arr class tests
 *
 * @group core
 * @group helpers
 */
class ArrTest extends PlatineTestCase
{
    /**
     * @dataProvider commonDataProvider
     * @param string $method method to call
     * @param array<int, mixed> $args methods arguments
     * @param mixed $expected
     * @return void
     */
    public function testCommonMethods(string $method, array $args, $expected): void
    {
        $this->assertEquals($expected, Arr::$method(...$args));
    }

    /**
     * Data provider for "testCommonMethods"
     * @return array
     */
    public function commonDataProvider(): array
    {
        return [
          [
              'first',
              [[2, 4, 5]],
              2
          ],
          [
              'first',
              [[1, 4, 5]],
              1
          ],
          [
              'first',
              [[], null, 5],
              5
          ],
          [
              'first',
              [[], function () {
              }, 5],
              5
          ],
          [
              'first',
              [['1', 'on', 'two'], function ($v, $k) {
                return strlen($v) >= 3;
              }],
              'two'
          ],
          [
              'last',
              [[], null, 5],
              5
          ],
          [
              'last',
              [[1, 4, 5]],
              5
          ],
          [
              'last',
              [['1', 'on', 'two'], function ($v, $k) {
                return strlen($v) === 2;
              }],
              'on'
          ],
          [
              'where',
              [['1', 'on', 'two'], function ($v, $k) {
                return strlen($v) === 1;
              }],
              ['1']
          ],
          [
              'query',
              [['a' => 'b', 'c' => 1]],
              'a=b&c=1'
          ],
          [
              'only',
              [[1, 4, 5], [0, 2]],
              [0 => 1, 2 => 5]
          ],
        ];
    }
}
