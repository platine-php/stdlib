<?php

declare(strict_types=1);

namespace Platine\Test\Stdlib\Helper;

use Platine\Dev\PlatineTestCase;
use Platine\Stdlib\Helper\Env;

/**
 * Env class tests
 *
 * @group core
 * @group helpers
 */
class EnvTest extends PlatineTestCase
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

        $this->assertEquals($expected, Env::$method(...$args));
    }

    /**
     * Data provider for "testCommonMethods"
     * @return array
     */
    public function commonDataProvider(): array
    {
        return [
          [
              'isCli',
              [],
              ['mock_php_sapi_name_to_cli'],
              true
          ],
          [
              'isCli',
              [],
              ['mock_php_sapi_name_to_foo'],
              false
          ],
          [
              'isPhpDbg',
              [],
              ['mock_php_sapi_name_to_phpdbg'],
              true
          ],
          [
              'isPhpDbg',
              [],
              ['mock_php_sapi_name_to_foo'],
              false
          ],
          [
              'isCygwin',
              [],
              ['mock_php_stripos_to_cygwin'],
              true
          ],
          [
              'isCygwin',
              [],
              ['mock_php_stripos_to_foo'],
              false
          ],
          [
              'isWindows',
              [],
              ['mock_php_stripos_to_win'],
              true
          ],
          [
              'isWindows',
              [],
              ['mock_php_stripos_to_foo'],
              false
          ],
          [
              'isMac',
              [],
              ['mock_php_stripos_to_mac'],
              true
          ],
          [
              'isMac',
              [],
              ['mock_php_stripos_to_foo'],
              false
          ],
        ];
    }
}
