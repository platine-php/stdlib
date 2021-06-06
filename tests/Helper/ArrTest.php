<?php

declare(strict_types=1);

namespace Platine\Test\Stdlib\Helper;

use ArrayAccess;
use ArrayIterator;
use InvalidArgumentException;
use Platine\Dev\PlatineTestCase;
use Platine\Stdlib\Contract\Arrayable;
use Platine\Stdlib\Helper\Arr;
use stdClass;
use Traversable;

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

    public function testForget(): void
    {
        $arr = [1, 4, 5];
        Arr::forget($arr, 1);
        $this->assertEquals([0 => 1, 2 => 5], $arr);
    }

    public function testForgetEmptyKeys(): void
    {
        $arr = [1, 4, 5];
        Arr::forget($arr, []);
        $this->assertEquals([1, 4, 5], $arr);
    }

    public function testForgetDotNotation(): void
    {
        $arr = ['a' => ['b' => 1], 4, 5];
        Arr::forget($arr, 'a.b');
        $this->assertEquals([0 => 4, 1 => 5, 'a' => []], $arr);
    }

    public function testForgetDotNotationNotExists(): void
    {
        $arr = ['a' => ['b' => 1], 4, 5];
        Arr::forget($arr, 'a.b.c');
        $this->assertEquals(['a' => ['b' => 1], 4, 5], $arr);
    }

    public function testPull(): void
    {
        $arr = [1, 4, 5];
        $val = Arr::pull($arr, 1);
        $this->assertEquals(4, $val);
        $this->assertEquals([0 => 1, 2 => 5], $arr);
    }

    public function testMultiSort(): void
    {
        $arr = [['a' => 115, 'b' => 4], ['a' => 11, 'b' => 14]];
        Arr::multisort($arr, 'a');
        $this->assertEquals([['a' => 11, 'b' => 14], ['a' => 115, 'b' => 4]], $arr);
    }

    public function testMultiSortEmptyKeysOrArray(): void
    {
        $arr = [['a' => 115, 'b' => 4], ['a' => 11, 'b' => 14]];
        Arr::multisort($arr, '');
        $this->assertEquals([['a' => 115, 'b' => 4], ['a' => 11, 'b' => 14]], $arr);

        $empty = [];
        Arr::multisort($empty, 'a');
        $this->assertEquals([], $empty);
    }

    public function testMultiSortDirectionCountError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $arr = [['a' => 115, 'b' => 4], ['a' => 11, 'b' => 14]];
        Arr::multisort($arr, 'a', [1, 4]);
    }

    public function testSetKeyIsNull(): void
    {
        $arr = [1, 4, 46];
        Arr::set($arr, null, 1);
        $this->assertEquals([1, 4, 46], $arr);
    }

    public function testSet(): void
    {
        $arr = [1, 4, 46];
        Arr::set($arr, 'a', 1);
        $this->assertEquals([1, 4, 46, 'a' => 1], $arr);
    }

    public function testSetDotNotation(): void
    {
        $arr = [];
        Arr::set($arr, 'a.b', 1);
        $this->assertEquals(['a' => ['b' => 1]], $arr);
    }

    public function testInsert(): void
    {
        $arr = [0];
        Arr::insert($arr, 1, [1], [2], [3]);
        $this->assertEquals([0, [1], [2], [3]], $arr);
    }

    public function testMultiSortSortFlagCountError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $arr = [['a' => 115, 'b' => 4], ['a' => 11, 'b' => 14]];
        Arr::multisort($arr, 'a', [1], [1, 4]);
    }

    public function testRandomRequestedCountError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $arr = [['a' => 115, 'b' => 4], ['a' => 11, 'b' => 14]];
        Arr::random($arr, 34);
    }

    public function testRandomKeyIsNull(): void
    {
        global $mock_array_rand_to_1;
        $mock_array_rand_to_1 = true;
        $arr = [1, 4, 42];
        $res = Arr::random($arr, null);
        $this->assertEquals(4, $res);
    }

    public function testRandom(): void
    {
        global $mock_array_rand_to_1;
        $mock_array_rand_to_1 = true;
        $arr = [1, 4, 42];
        $res = Arr::random($arr, 1);
        $this->assertEquals([4], $res);
    }

    public function testRandomRequestedIsZero(): void
    {
        $res = Arr::random([], 0);
        $this->assertEquals([], $res);
    }

    public function testShuffleSeedIsNull(): void
    {
        global $mock_mt_srand_to_void,
                $mock_shuffle_to_1;

        $mock_shuffle_to_1 = true;
        $mock_mt_srand_to_void = true;

        $res = Arr::shuffle([4], null);
        $this->assertEquals([4], $res);
    }

    public function testShuffle(): void
    {
        global $mock_mt_srand_to_void,
                $mock_shuffle_to_1;

        $mock_shuffle_to_1 = true;
        $mock_mt_srand_to_void = true;

        $res = Arr::shuffle([4, 1, 78], 10);
        $this->assertEquals([4], $res);
    }

    public function testNormalizeArgumentsDefault(): void
    {
        $res = Arr::normalizeArguments(['--abc=123']);

        $this->assertCount(2, $res);
        $this->assertEquals('--abc', $res[0]);
        $this->assertEquals('123', $res[1]);

        $res1 = Arr::normalizeArguments(['-abc']);

        $this->assertCount(3, $res1);
        $this->assertEquals('-a', $res1[0]);
        $this->assertEquals('-b', $res1[1]);
        $this->assertEquals('-c', $res1[2]);

        $res2 = Arr::normalizeArguments(['-p', '123']);

        $this->assertCount(2, $res2);
        $this->assertEquals('-p', $res2[0]);
        $this->assertEquals('123', $res2[1]);

        $res3 = Arr::normalizeArguments(['-p=1']);

        $this->assertCount(2, $res3);
        $this->assertEquals('-p', $res3[0]);
        $this->assertEquals('1', $res3[1]);
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
          [
              'toArray',
              ['foo', [], true],
              ['foo']
          ],
          [
              'toArray',
              [$this->getTestObject(), [], true],
              ['a' => 1, 'b' => ['a' => 2]]
          ],
          [
              'toArray',
              [$this->getTestObject(), [stdClass::class => ['a']], true],
              ['a' => 1]
          ],
          [
              'toArray',
              [$this->getTestObject(), [stdClass::class => ['b' => 'a']], true],
              ['b' => 1]
          ],
          [
              'toArray',
              [$this->getArrayableObject(), [], true],
              [1, 2, 3]
          ],
          [
            'merge',
            [[1, 4, 5], [0, 2]],
            [1, 4, 5, 0, 2]
          ],
          [
            'merge',
            [[1, 4, 'c' => [3]], ['c' => ['a' => 45], 2]],
            [1, 4, 'c' => [3, 'a' => 45], 2]
          ],
          [
            'merge',
            [[1, 4, 5], ['a' => 4, 2]],
            [1, 4, 5, 'a' => 4, 2]
          ],
          [
            'getValue',
            [[1, 4, 5], function ($v, $d) {
                return 5;
            }],
            5
          ],
          [
            'getValue',
            [[1, 4, 5], 1],
            4
          ],
          [
            'getValue',
            [[1, 4, 5], [0, 1]],
            null
          ],
          [
            'getValue',
            [[[4, 5], 6, 5], [0, 1]],
            5
          ],
          [
            'getValue',
            [['a' => ['b' => 4], 6, 5], 'a.b'],
            4
          ],
          [
            'getValue',
            [$this->getTestObject(), 'a'],
            1
          ],
          [
            'remove',
            [[1, 4, 5], 1],
            4
          ],
          [
            'remove',
            [[1, 4, 5], 10, 'x'],
           'x'
          ],
          [
            'except',
            [[1, 4, 5], 1],
            [0 => 1, 2 => 5]
          ],
          [
            'index',
            [[1, 4, 5], [3, 2], 0],
            ['' => null]
          ],
          [
            'index',
            [[[1, 4, 5], [3, 2]], null, [1]],
            [4 => [0 => [1, 4, 5]], 2 => [0 => [3, 2]]]
          ],
          [
            'index',
            [[[1, 4, 5], [3, 2.7]], 1],
            [4 => [1, 4, 5], '2.7' => [3, 2.7]]
          ],
          [
            'index',
            [[['a' => 10, 'b' => 34], ['a' => 2, 'b' => 15]], 'a'],
            [
                2 => ['a' => 2, 'b' => 15],
                10 => ['a' => 10, 'b' => 34]
            ]
          ],
          [
            'getColumn',
            [[['a' => 10, 'b' => 34], ['a' => 2, 'b' => 15]], 'a'],
            [10, 2]
          ],
          [
            'getColumn',
            [[['a' => 10, 'b' => 34], ['a' => 2, 'b' => 15]], 'a', false],
            [10, 2]
          ],
          [
            'map',
            [[['a' => 10, 'b' => 34], ['a' => 2, 'b' => 15]], 'a', 'b', null],
            [10 => 34, 2 => 15]
          ],
          [
            'map',
            [[['a' => 10, 'b' => 34], ['a' => 2, 'b' => 15]], 'a', 'b', ['a']],
            [10 => [10 => 34], 2 => [2 => 15]]
          ],
          [
            'keyExists',
            ['a', ['a' => 10, 'b' => 34], true],
            true
          ],
          [
            'keyExists',
            ['A', ['a' => 10, 'b' => 34], true],
            false
          ],
          [
            'keyExists',
            ['A', ['a' => 10, 'b' => 34], false],
            true
          ],
          [
            'keyExists',
            ['c', ['a' => 10, 'b' => 34], false],
            false
          ],
          [
            'isAssoc',
            [['a' => 10, 'b' => 34], true],
            true
          ],
          [
            'isAssoc',
            [[5, 3, 'a' => 10, 'b' => 34], false],
            true
          ],
          [
            'isAssoc',
            [[5, 3, 'a' => 10, 'b' => 34], true],
            false
          ],
          [
            'isAssoc',
            [[5, 3], false],
            false
          ],
          [
            'isAssoc',
            [[]],
            false
          ],
          [
            'isIndexed',
            [[5, 3], false],
            true
          ],
          [
            'isIndexed',
            [[], false],
            true
          ],
          [
            'isIndexed',
            [[5, 3], true],
            true
          ],
          [
            'isIndexed',
            [[1 => 5, 3], true],
            false
          ],
          [
            'isIndexed',
            [['1' => 5, 3], true],
            false
          ],
          [
            'isIndexed',
            [['a1' => 5, 3], false],
            false
          ],
          [
            'isIn',
            [1, ['1' => 5, 3], true],
            false
          ],
          [
            'isIn',
            [1, ['1', 5, 3], true],
            false
          ],
          [
            'isIn',
            [1, ['1', 5, 3], false],
            true
          ],
          [
            'isIn',
            [1, [1, 5, 3], true],
            true
          ],
          [
            'isIn',
            [3, $this->getTestTraversable(), true],
            true
          ],
          [
            'isTraversable',
            [$this->getTestTraversable()],
            true
          ],
          [
            'isTraversable',
            [[]],
            true
          ],
          [
            'isTraversable',
            [1],
            false
          ],
          [
            'isAccessible',
            [$this->getTestArrayAccess()],
            true
          ],
          [
            'isAccessible',
            [[]],
            true
          ],
          [
            'isAccessible',
            [1],
            false
          ],
          [
            'isArrayable',
            [$this->getArrayableObject()],
            true
          ],
          [
            'isArrayable',
            [[]],
            true
          ],
          [
            'isArrayable',
            [1],
            false
          ],
          [
            'isSubset',
            [[1, 5], [1, 5, 3], true],
            true
          ],
          [
            'isSubset',
            [[1, 5, 4], [1, 5, 3], true],
            false
          ],
          [
            'isSubset',
            [[1, '5'], [1, 5, 3], false],
            true
          ],
          [
            'isSubset',
            [[1, 3], $this->getTestTraversable(), true],
            true
          ],
          [
            'wrap',
            [1],
            [1]
          ],
          [
            'wrap',
            [null],
            []
          ],
          [
            'wrap',
            [[1]],
            [1]
          ],
          [
            'exists',
            [$this->getTestArrayAccess(), 0],
            true
          ],
          [
            'exists',
            [$this->getTestArrayAccess(), 10],
            false
          ],
          [
            'exists',
            [[1, 4], 0],
            true
          ],
          [
            'exists',
            [[1, 4], 2],
            false
          ],
          [
            'get',
            [[1, 4], 1],
            4
          ],
          [
            'get',
            [[1, 4], null],
            [1, 4]
          ],
          [
            'get',
            [[1, 4], 4, 'x'],
            'x'
          ],
          [
            'get',
            [['a' => ['b' => 5], 4], 'a.b', 'x'],
            5
          ],
          [
            'get',
            [['a' => ['b' => 5], 4], 'a.b.c', 'x'],
            'x'
          ],
          [
            'filter',
            [['a' => ['b' => 5], 4], ['a.b.c']],
            ['a' => ['b' => 5]]
          ],
          [
            'filter',
            [['a' => ['b' => 5], 4], ['a.b']],
            ['a' => ['b' => 5]]
          ],
          [
            'filter',
            [['a' => ['b' => 5], 4], ['!a.b']],
            []
          ],
          [
            'filter',
            [['a' => ['b' => 5], 4], ['c']],
            []
          ],
          [
            'filter',
            [['a' => ['b' => 5], 4], ['a']],
            ['a' => ['b' => 5]]
          ],
          [
            'filter',
            [['a' => ['b' => 5]], ['a.b']],
            ['a' => ['b' => 5]]
          ],
          [
            'filter',
            [['a' => ['b' => 5]], ['a.b', 'a.c']],
            ['a' => ['b' => 5]]
          ],
          [
            'filter',
            [['a' => ['b' => 5]], ['a.b', 'a.b.c']],
            ['a' => ['b' => 5]]
          ],
          [
            'filter',
            [['a' => ['b' => 5]], ['a.b', '!a.b']],
            ['a' => []]
          ],
          [
            'has',
            [[], 'a.b'],
            false
          ],
          [
            'has',
            [$this->getTestArrayAccess(), 1],
            true
          ],
          [
            'has',
            [[3, 4, 5], 7],
            false
          ],
          [
            'has',
            [[3, 4, 5], 1],
            true
          ],
          [
            'has',
            [['a' => ['b' => 3], 4, 5], 'a.b'],
            true
          ],
          [
            'has',
            [['a' => ['b' => 3], 4, 5], 'a.b.c'],
            false
          ],
          [
            'flatten',
            [[[1], [4, 5]]],
            [1, 4, 5]
          ],
          [
            'flatten',
            [[[1], [4, 5]], 1],
            [1, 4, 5]
          ],
           [
            'similar',
            ['tnh', ['a', 'b', 'tn', 'd', 'nh']],
            ['tn', 'nh']
          ],
          [
            'similar',
            ['', [1, 6]],
            []
          ],
          [
            'getKeyMaxWidth',
            [[1, 6]],
            1
          ],
          [
            'getKeyMaxWidth',
            [[1, 6], true],
            0
          ],
          [
            'getKeyMaxWidth',
            [['a' => 6, 'bc' => 5, 'abc' => 89]],
            3
          ],
          [
            'pluck',
            [[89, [3, 8], 6], 3],
            [null]
          ],
          [
            'pluck',
            [[89, 3, 6], 3, 2],
            []
          ],
          [
            'pluck',
            [[89, [$this->getTestObjectToString()], 6], 3, 0],
            ['toString' => null]
          ],
          [
            'pluck',
            [[[89, 3, 6]], 2, 1],
            [3 => 6]
          ],
          [
            'pluck',
            [[[89, 3, 6]], 2],
            [6]
          ],
          [
            'collapse',
            [[[89, 3, 6]]],
            [89, 3, 6]
          ],
          [
            'collapse',
            [[[89, 3, 6], 5, [34]]],
            [89, 3, 6, 34]
          ],
          [
            'crossJoin',
            [[1, 2], [3, 4]],
            [[1, 3], [1, 4], [2, 3], [2, 4]]
          ],
          [
            'prepend',
            [[89, 3, 6], 5, null],
            [5, 89, 3, 6]
          ],
          [
            'prepend',
            [[89, 3, 6], 5, 1],
            [89, 5, 6]
          ],
          [
            'toString',
            [[89, 3, 6]],
            '89_3_6'
          ],
          [
            'toString',
            [[89, 3, 6], '|'],
            '89|3|6'
          ],
        ];
    }

    private function getTestObject()
    {
        $cls1 = new stdClass();
        $cls1->a = 1;

        $cls2 = new stdClass();
        $cls2->a = 2;

        $cls1->b = $cls2;

        return $cls1;
    }

    private function getTestTraversable(): Traversable
    {
        $ai = new ArrayIterator([1, 3, 4]);

        return $ai;
    }

    private function getTestArrayAccess(): ArrayAccess
    {
        $aa =  new class implements ArrayAccess{

            private $arr = [1, 4, 5];

            public function offsetExists($offset): bool
            {
                return isset($this->arr[$offset]);
            }

            public function offsetGet($offset)
            {
                return $this->arr[$offset];
            }

            public function offsetSet($offset, $value): void
            {
                $this->arr[$offset] = $value;
            }

            public function offsetUnset($offset): void
            {
                unset($this->arr[$offset]);
            }
        };

        return $aa;
    }

    private function getArrayableObject()
    {
        $cls = new class implements Arrayable{

            public function toArray(): array
            {
                return [1,2,3];
            }
        };

        return $cls;
    }

    private function getTestObjectToString()
    {
        $cls = new class {

            public function __toString(): string
            {
                return 'toString';
            }
        };

        return $cls;
    }
}
