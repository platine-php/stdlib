<?php

declare(strict_types=1);

namespace Platine\Test\Stdlib\Helper;

use Composer\Autoload\ClassLoader;
use Platine\PlatineTestCase;
use Platine\Stdlib\Helper\Composer;
use RuntimeException;

/**
 * Composer class tests
 *
 * @group core
 * @group helpers
 */
class ComposerTest extends PlatineTestCase
{
    public function testGetClassLoaderNotFound(): void
    {
        global $mock_spl_autoload_functions_to_empty;
        $mock_spl_autoload_functions_to_empty = true;

        $this->expectException(RuntimeException::class);

        Composer::getClasstLoader();
    }

    public function testGetClassLoaderSuccess(): void
    {
        global $mock_spl_autoload_functions_to_array;
        $mock_spl_autoload_functions_to_array = true;

        $cls = Composer::getClasstLoader();
        $this->assertInstanceOf(ClassLoader::class, $cls);
        //Already cached
        $this->assertEquals(Composer::getClasstLoader(), $cls);
    }

    public function testParseLockFileFileDoesNotExists(): void
    {
        global $mock_file_exists_to_false;
        $mock_file_exists_to_false = true;

        $this->expectException(RuntimeException::class);

        Composer::parseLockFile('path/does/not/exist');
    }

    public function testParseLockFileFileCannotGetContent(): void
    {
        global $mock_file_exists_to_true,
               $mock_file_get_contents_to_false;
        $mock_file_exists_to_true = true;
        $mock_file_get_contents_to_false = true;

        $packages = Composer::parseLockFile('project/foo/bar/test');
        $this->assertEquals([], $packages);
    }

    public function testParseLockFileFileContentEmpty(): void
    {
        global $mock_file_exists_to_true,
               $mock_file_get_contents_to_foo,
               $mock_json_decode_to_empty;
        $mock_file_exists_to_true = true;
        $mock_file_get_contents_to_foo = true;
        $mock_json_decode_to_empty = true;

        $packages = Composer::parseLockFile('project/foo/bar/test');
        $this->assertEquals([], $packages);
    }

    public function testParseLockFileFileWithoutFilter(): void
    {
        global $mock_file_exists_to_true,
               $mock_file_get_contents_to_foo,
               $mock_json_decode_to_array;
        $mock_file_exists_to_true = true;
        $mock_file_get_contents_to_foo = true;
        $mock_json_decode_to_array = true;

        $packages = Composer::parseLockFile('project/foo/bar/test');
        $this->assertCount(1, $packages);
        $this->assertIsArray($packages);
        $this->assertArrayHasKey('name', $packages[0]);
        $this->assertArrayHasKey('type', $packages[0]);
        $this->assertEquals('library', $packages[0]['type']);
        $this->assertEquals('foo', $packages[0]['name']);
    }

    public function testParseLockFileFileWithFilter(): void
    {
        global $mock_file_exists_to_true,
               $mock_file_get_contents_to_foo,
               $mock_json_decode_to_array;
        $mock_file_exists_to_true = true;
        $mock_file_get_contents_to_foo = true;
        $mock_json_decode_to_array = true;

        $packages = Composer::parseLockFile('project/foo/bar/test', function ($name, $type) {
            return false;
        });
        $this->assertEquals([], $packages);
    }
}
