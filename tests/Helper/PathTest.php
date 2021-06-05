<?php

declare(strict_types=1);

namespace Platine\Test\Stdlib\Helper;

use InvalidArgumentException;
use Platine\PlatineTestCase;
use Platine\Stdlib\Helper\Path;

/**
 * Path class tests
 *
 * @group core
 * @group helpers
 */
class PathTest extends PlatineTestCase
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

        $this->assertEquals($expected, Path::$method(...$args));
    }

    public function testRealPathSuccess(): void
    {
        global $mock_realpath_to_foo;
        $mock_realpath_to_foo = true;

        $this->assertEquals(Path::realPath('my_file.php'), 'foo');
    }

    public function testRealPathError(): void
    {
        global $mock_realpath_to_false;
        $mock_realpath_to_false = true;
        $this->expectException(InvalidArgumentException::class);

        Path::realPath('/path/does/not/exist');
    }

    /**
     * Data provider for "testCommonMethods"
     * @return array
     */
    public function commonDataProvider(): array
    {
        return [
          [
              'normalizePath',
              ['foo/bar', true],
              [],
              'foo/bar/'
          ],
          [
              'normalizePath',
              ['foo\bar', true],
              [],
              'foo/bar/'
          ],
          [
              'normalizePath',
              ['foo\bar', false],
              [],
              'foo/bar'
          ],
          [
              'normalizePathDS',
              ['foo\bar', false],
              [],
              sprintf('foo%sbar', DIRECTORY_SEPARATOR)
          ],
          [
              'normalizePathDS',
              ['foo/bar', false],
              [],
              sprintf('foo%sbar', DIRECTORY_SEPARATOR)
          ],
          [
              'normalizePathDS',
              ['foo\bar', true],
              [],
              sprintf('foo%sbar%s', DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR)
          ],
          [
              'isAbsolutePath',
              ['foo\bar'],
              [],
              false
          ],
          [
              'isAbsolutePath',
              [''],
              [],
              false
          ],
          [
              'isAbsolutePath',
              ['c:/foo\bar'],
              [],
              true
          ],
          [
              'isAbsolutePath',
              ['/foo\bar'],
              [],
              true
          ],
          [
              'convert2Absolute',
              ['foo\bar', true],
              [],
              'foo/bar'
          ],
          [
              'convert2Absolute',
              ['foo\bar\..\baz', true],
              [],
              'foo/baz'
          ],
          [
              'convert2Absolute',
              ['foo\bar\.\baz\..\foobar', true],
              [],
              'foo/bar/foobar'
          ],
          [
              'getMimeByExtension',
              ['pdf'],
              [],
              'application/pdf'
          ],
          [
              'getMimeByExtension',
              ['tnh'],
              [],
              'text/plain'
          ],
        ];
    }
}
