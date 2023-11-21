<?php

declare(strict_types=1);

namespace Platine\Test\Stdlib\Helper;

use InvalidArgumentException;
use Platine\Dev\PlatineTestCase;
use Platine\Stdlib\Helper\Uuid;

/**
 * Php class tests
 *
 * @group core
 * @group helpers
 */
class UuidTest extends PlatineTestCase
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

        $this->assertEquals($expected, Uuid::$method(...$args));
    }

    public function testInvalidNamespaceV3(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Uuid::v3('name', 'name');
    }

    public function testInvalidNamespaceV5(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Uuid::v5('name', 'name');
    }

    /**
     * Data provider for "testCommonMethods"
     * @return array
     */
    public function commonDataProvider(): array
    {
        return [
          [
              'v3',
              ['1546058f-5a25-4334-85ae-e68f2a44bbaf', '1773-3883'],
              ['mock_mt_rand_to_zero'],
              '4231e78e-b5f8-3164-8f5f-8219309a0a33'
          ],
          [
              'v4',
              [],
              ['mock_mt_rand_to_zero'],
              '00000000-0000-4000-8000-000000000000'
          ],
          [
              'v5',
              ['1546058f-5a25-4334-85ae-e68f2a44bbaf', '1773-3883'],
              ['mock_mt_rand_to_zero'],
              '0eb53739-e03c-53f5-b4f2-13723388f865'
          ],
        ];
    }
}
