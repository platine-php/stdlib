<?php

declare(strict_types=1);

namespace Platine\Test\Stdlib\Helper;

use ArrayIterator;
use DateTime;
use Exception;
use Platine\Dev\PlatineTestCase;
use Platine\Stdlib\Helper\Str;
use Platine\Test\Fixture\Stdlib\Stringify__toString;
use Platine\Test\Fixture\Stdlib\StringifyJson;
use Platine\Test\Fixture\Stdlib\StringifytoString;
use stdClass;

/**
 * Str class tests
 *
 * @group core
 * @group helpers
 */
class StrTest extends PlatineTestCase
{
    public function testDefaultIP(): void
    {
        $ip = Str::ip();
        $this->assertEquals('127.0.0.1', $ip);
    }

    public function testSuccess(): void
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';

        $ip = Str::ip();
        $this->assertEquals('192.168.1.1', $ip);
    }

    public function testManyIpAddresses(): void
    {
        $_SERVER['REMOTE_ADDR'] = '172.18.0.1,192.168.1.1';

        $ip = Str::ip();
        $this->assertEquals('172.18.0.1', $ip);
    }

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

        $this->assertEquals($expected, Str::$method(...$args));
    }

    public function testCamelCached(): void
    {
        $res = Str::camel('a_b');
        $this->assertEquals('aB', $res);
        $this->assertEquals('aB', Str::camel('a_b'));
        $this->assertEquals('aBP', Str::camel('a_b-p'));
    }

    public function testSnakeCached(): void
    {
        $res = Str::snake('aBC');
        $this->assertEquals('a_b_c', $res);
        $this->assertEquals('a_b_c', Str::snake('aBC'));
        $this->assertEquals('a_b_c', Str::snake('a_b_c'));
        $this->assertEquals('a-b-c', Str::snake('aBC', '-'));
    }

    public function testStudlyCached(): void
    {
        $res = Str::studly('aBC');
        $this->assertEquals('ABC', $res);
        $this->assertEquals('ABC', Str::studly('a_b_c'));
        $this->assertEquals('ABCore', Str::studly('aB_Core'));
    }

    public function testStringifyResource(): void
    {
        $fp = fopen('php://temp', 'r');
        $str = Str::stringify($fp);
        fclose($fp);
        $this->assertEquals('resource<stream>', $str);
    }

    public function testStringifyDefault(): void
    {
        global $mock_is_object_to_false;
        $mock_is_object_to_false = true;
        $o = new stdClass();
        $str = Str::stringify($o);
        $this->assertEquals('object', $str);
    }

    public function testStringifyThrowable(): void
    {
        $ex = new Exception('My exception');
        $str = Str::stringify($ex);
        $this->assertEquals(
            'Exception { "My exception", 0, ' . __FILE__ . ' #116 }',
            $str
        );
    }

    /**
     * Data provider for "testCommonMethods"
     * @return array
     */
    public function commonDataProvider(): array
    {
        return [
          [
              'toAscii',
              ['aé'],
              [],
              'ae'
          ],
          [
              'stringify',
              [null],
              [],
              'null'
          ],
          [
              'stringify',
              [true],
              [],
              'true'
          ],
          [
              'stringify',
              [false],
              [],
              'false'
          ],
          [
              'stringify',
              ['foo'],
              [],
              'foo'
          ],
          [
              'stringify',
              [34],
              [],
              '34'
          ],
          [
              'stringify',
              [34.5],
              [],
              '34.5'
          ],
          [
              'stringify',
              [[]],
              [],
              '[]'
          ],
          [
              'stringify',
              [[1, 3, 4]],
              [],
              '[1, 3, 4]'
          ],
          [
              'stringify',
              [[1, 3, 4, 'name' => 'foo']],
              [],
              '[1, 3, 4, foo]'
          ],
          [
              'stringify',
              [['foo' => 34, 1, 3, 4]],
              [],
              '[foo => 34, 0 => 1, 1 => 3, 2 => 4]'
          ],
          [
              'stringify',
              [new stdClass()],
              [],
              stdClass::class
          ],
          [
              'stringify',
              [new ArrayIterator([1, 3, 5])],
              [],
              'ArrayIterator [1, 3, 5]'
          ],
          [
              'stringify',
              [new DateTime('2021-06-27')],
              [],
              'DateTime { 2021-06-27T00:00:00+00:00 }'
          ],
          [
              'stringify',
              [new Stringify__toString()],
              [],
              sprintf('%s { %s }', Stringify__toString::class, Stringify__toString::class)
          ],
          [
              'stringify',
              [new StringifytoString()],
              [],
              sprintf('%s { %s }', StringifytoString::class, StringifytoString::class)
          ],
          [
              'stringify',
              [new StringifyJson()],
              [],
              sprintf('%s {[1,2,3]}', StringifyJson::class)
          ],
          [
              'camel',
              ['a_b_c'],
              [],
              'aBC'
          ],
          [
              'toArray',
              ['a_bv_c', '_'],
              [],
              ['a', 'bv', 'c']
          ],
          [
              'toArray',
              [' ', '_'],
              [],
              []
          ],
          [
              'toArray',
              ['a_bv_c', '_', 2],
              [],
              ['a', 'bv_c']
          ],
          [
              'contains',
              ['a', 'abc'],
              [],
              true
          ],
          [
              'contains',
              ['i', ['abc', 'i']],
              [],
              true
          ],
          [
              'contains',
              ['ab', 'abc'],
              [],
              true
          ],
          [
              'contains',
              ['b', 'abc'],
              [],
              true
          ],
          [
              'contains',
              ['c', 'abc'],
              [],
              true
          ],
          [
              'contains',
              ['ac', 'abc'],
              [],
              false
          ],
          [
              'contains',
              ['i', 'abc'],
              [],
              false
          ],
          [
              'endsWith',
              ['b', 'abc'],
              [],
              false
          ],
          [
              'endsWith',
              ['a', 'abc'],
              [],
              false
          ],
          [
              'endsWith',
              ['c', 'abc'],
              [],
              true
          ],
          [
              'endsWith',
              ['bc', 'abc'],
              [],
              true
          ],
          [
              'startsWith',
              ['b', 'abc'],
              [],
              false
          ],
          [
              'startsWith',
              ['a', 'abc'],
              [],
              true
          ],
          [
              'startsWith',
              ['c', 'abc'],
              [],
              false
          ],
          [
              'startsWith',
              ['abc', 'abc'],
              [],
              true
          ],
          [
              'startsWith',
              ['ab', 'abc'],
              [],
              true
          ],
          [
              'firstLine',
              [' '],
              [],
              ''
          ],
          [
              'firstLine',
              ['ab'],
              [],
              'ab'
          ],
          [
              'firstLine',
              ["a\nb"],
              [],
              'a'
          ],
          [
              'finish',
              ['ab', 'c'],
              [],
              'abc'
          ],
          [
              'finish',
              ['cabcc', 'c'],
              [],
              'cabc'
          ],
          [
              'finish',
              ['cabccccc', 'cc'],
              [],
              'cabccc'
          ],
          [
              'is',
              ['cc', 'cc'],
              [],
              true
          ],
          [
              'is',
              ['library/*', 'library/foo'],
              [],
              true
          ],
          [
              'is',
              ['a-c/*', 'library/foo'],
              [],
              false
          ],
          [
              'length',
              [123333],
              [],
              6
          ],
          [
              'length',
              ['2é('],
              [],
              3
          ],
          [
              'length',
              [null],
              [],
              0
          ],
          [
              'padLeft',
              [123, 3],
              [],
              '123'
          ],
          [
              'padLeft',
              ['123', 5, 'x'],
              [],
              'xx123'
          ],
          [
              'padRight',
              ['123', 5, '*'],
              [],
              '123**'
          ],
          [
              'repeat',
              [123, 3],
              [],
              '123123123'
          ],
          [
              'repeat',
              ['x', 3],
              [],
              'xxx'
          ],
          [
              'limit',
              ['abcde', 3],
              [],
              'abc...'
          ],
          [
              'limit',
              ['abcde', 2, '*'],
              [],
              'ab*'
          ],
          [
              'limit',
              ['ab', 2, '*'],
              [],
              'ab'
          ],
          [
              'words',
              ['ab', 2, '*'],
              [],
              'ab'
          ],
          [
              'words',
              ['foo bar baz', 2],
              [],
              'foo bar...'
          ],
          [
              'words',
              ['foo bar baz', 1, '*'],
              [],
              'foo*'
          ],
          [
              'replaceFirst',
              ['a', 'b', 'foobarbaz'],
              [],
              'foobbrbaz'
          ],
          [
              'replaceFirst',
              ['c', 'b', 'foobarbaz'],
              [],
              'foobarbaz'
          ],
          [
              'replaceLast',
              ['a', 'b', 'foobarbaz'],
              [],
              'foobarbbz'
          ],
          [
              'replaceLast',
              ['c', 'b', 'foobarbaz'],
              [],
              'foobarbaz'
          ],
          [
              'title',
              ['foo bar baz'],
              [],
              'Foo Bar Baz'
          ],
          [
              'title',
              ['foo_bar baz'],
              [],
              'Foo_Bar Baz'
          ],
          [
              'slug',
              ['foo_bar baz'],
              [],
              'foo-bar-baz'
          ],
          [
              'slug',
              ['fooéïbar 9-6yu-'],
              [],
              'fooeibar-9-6yu'
          ],
          [
              'substr',
              ['foobar', 1],
              [],
              'oobar'
          ],
          [
              'substr',
              ['foobar', -1, 2],
              [],
              'r'
          ],
          [
              'substr',
              ['foobar', 2, 2],
              [],
              'ob'
          ],
          [
              'ucfirst',
              ['foobar'],
              [],
              'Foobar'
          ],
          [
              'ucfirst',
              ['foo bar'],
              [],
              'Foo bar'
          ],
          [
              'split',
              ['foo bar', 0],
              [],
              []
          ],
          [
              'split',
              ['foo bar', 2],
              [],
              ['fo', 'o ', 'ba', 'r']
          ],
          [
              'split',
              ['foé', 3],
              [],
              ['foé']
          ],
          [
              'split',
              ['foé', 2],
              [],
              ['fo', 'é']
          ],
          [
              'split',
              ['foa', 2],
              ['mock_str_split_to_false'],
              []
          ],
          [
              'lower',
              ['fOé'],
              [],
              'foé'
          ],
          [
              'upper',
              ['fOé'],
              [],
              'FOÉ'
          ],
          [
              'randomToken',
              [5, 'tnh'],
              ['mock_random_int', 'mock_md5_to_param'],
              '44444tnh'
          ],
          [
              'randomString',
              ['alnum', 5],
              ['mock_random_int', 'mock_chr_to_param'],
              '00100'
          ],
          [
              'randomString',
              ['alnum', 12],
              ['mock_random_int', 'mock_chr_to_param', 'mock_ctype_alpha_to_true'],
              '100000000000'
          ],
          [
              'randomString',
              ['alpha', 5],
              ['mock_random_int', 'mock_chr_to_param'],
              'bbbbb'
          ],
          [
              'randomString',
              ['lowalnum', 5],
              ['mock_random_int', 'mock_chr_to_param'],
              '00000'
          ],
          [
              'randomString',
              ['hexdec', 5],
              ['mock_random_int', 'mock_chr_to_param'],
              '11111'
          ],
          [
              'randomString',
              ['numeric', 5],
              ['mock_random_int', 'mock_chr_to_param'],
              '11111'
          ],
          [
              'randomString',
              ['nozero', 6],
              ['mock_random_int', 'mock_chr_to_param'],
              '333333'
          ],
          [
              'randomString',
              ['distinct', 1],
              ['mock_random_int', 'mock_chr_to_param'],
              '3'
          ],
          [
              'randomString',
              ['foo', 5],
              ['mock_random_int', 'mock_chr_to_param'],
              'ooooo'
          ],
          [
              'uniqId',
              [5],
              ['mock_random_bytes', 'mock_bin2hex_to_param'],
              'bar'
          ],
          [
              'uniqId',
              [4],
              ['mock_random_bytes', 'mock_bin2hex_to_param'],
              'foo'
          ],
          [
              'uniqId',
              [2],
              ['mock_random_bytes', 'mock_bin2hex_to_param'],
              'ba'
          ],
          [
              'random',
              [2],
              ['mock_random_bytes', 'mock_base64_encode_to_param'],
              'fo'
          ],
        ];
    }
}
