<?php

declare(strict_types=1);

namespace Platine\Test\Stdlib\Helper;

use Exception;
use Platine\PlatineTestCase;
use Platine\Stdlib\Helper\Php;
use Platine\Test\Fixture\Stdlib\phpCallClassCommon;
use Platine\Test\Fixture\Stdlib\phpCallClassInvokeCallback;
use Platine\Test\Fixture\Stdlib\phpCallClassMethodCallback;

/**
 * Php class tests
 *
 * @group core
 * @group helpers
 */
class PhpTest extends PlatineTestCase
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

        $this->assertEquals($expected, Php::$method(...$args));
    }

    public function testExceptionToArrayDebug(): void
    {
        $res = Php::exceptionToArray(new Exception('Foo Exception', 101), true);
        $execptionLine = __LINE__ - 1;

        $this->assertIsArray($res);
        $this->assertCount(4, $res);
        $this->assertArrayHasKey('code', $res);
        $this->assertArrayHasKey('error', $res);
        $this->assertArrayHasKey('file', $res);
        $this->assertArrayHasKey('trace', $res);
        $this->assertNotEmpty($res['trace']);
        $this->assertEquals(101, $res['code']);
        $this->assertEquals('(Exception) Foo Exception', $res['error']);
        $this->assertEquals(sprintf('at %s line %d', __FILE__, $execptionLine), $res['file']);
    }

    /**
     * Data provider for "testCommonMethods"
     * @return array
     */
    public function commonDataProvider(): array
    {
        return [
          [
              'call',
              ['\Platine\Test\Fixture\Stdlib\phpCallCallbackSimpleFunction'],
              [],
              1
          ],
          [
              'call',
              [[new phpCallClassMethodCallback(), 'foo'], 'baz'],
              [],
              'barbaz'
          ],
          [
              'call',
              [new phpCallClassInvokeCallback(), 'baz'],
              [],
              'barbaz'
          ],
          [
              'call',
              [[new phpCallClassCommon(), 'foo'], 'bar'],
              [],
              'barbar'
          ],
          [
              'call',
              [sprintf('%s::bar', phpCallClassCommon::class), 'bar'],
              [],
              'foobar'
          ],
          [
              'call',
              [function ($a) {
                return $a . '|';
              }, 'bar'],
              [],
              'bar|'
          ],
          [
              'callArray',
              [[phpCallClassCommon::class, 'bar'], ['bar']],
              [],
              'foobar'
          ],
          [
              'dumpVars',
              [3, 5.7, true, [1, 2, 3]],
              [],
              'int(3)
float(5.7)
bool(true)
array(3) {
  [0]=> int(1)
  [1]=> int(2)
  [2]=> int(3)
}
'
          ],
          [
              'printVars',
              [3, 5.7, true, [1, 2, 3]],
              [],
              '3
5.7
1
Array
(
    [0] => 1
    [1] => 2
    [2] => 3
)

'
          ],
          [
              'exportVar',
              [[1, 2, 3]],
              [],
              'array (
  0 => 1,
  1 => 2,
  2 => 3,
)'
          ],
          [
              'exportVar',
              [1],
              [],
              '1'
          ],
          [
              'exceptionToString',
              [new Exception('Foo Exception', 101)],
              [],
              ' Exception(code:101) Foo Exception'
          ],
          [
              'exceptionToString',
              [new Exception('Foo Exception', 101), 'ERROR'],
              [],
              'ERROR Exception(code:101) Foo Exception'
          ],
          [
              'exceptionToString',
              [new Exception('Foo Exception', 101), 'ERROR', true],
              [],
              sprintf(
                  'ERROR-Exception(code:101) Foo Exception at %s line %d',
                  __FILE__,
                  __LINE__ - 5
              )
          ],
          [
              'exceptionToArray',
              [new Exception('Foo Exception', 101), false],
              [],
              [
                  'code' => 101,
                  'error' => 'Foo Exception',
              ]
          ],
        ];
    }
}
