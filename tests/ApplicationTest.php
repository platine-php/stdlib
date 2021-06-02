<?php

declare(strict_types=1);

namespace Platine\Test\Console;

use org\bovigo\vfs\vfsStream;
use Platine\Console\Application;
use Platine\Console\Command\Command;
use Platine\Console\Exception\InvalidArgumentException;
use Platine\Console\IO\Interactor;
use Platine\Console\Output\Writer;
use Platine\PlatineTestCase;
use Platine\Test\Fixture\Console\MyColor;
use Platine\Test\Fixture\Console\MyCommand;
use Platine\Test\Fixture\Console\MyCommandExecuteThrowException;

/**
 * Application class tests
 *
 * @group core
 * @group console
 */
class ApplicationTest extends PlatineTestCase
{

    protected $vfsRoot;
    protected $vfsPath;

    protected $vfsOutputStream;

    protected function setUp(): void
    {
        parent::setUp();
        //need setup for each test
        $this->vfsRoot = vfsStream::setup();
        $this->vfsPath = vfsStream::newDirectory('my_tests')->at($this->vfsRoot);
        $this->vfsOutputStream = $this->createVfsFileOnly('stdout', $this->vfsPath);
    }

    public function testDefault(): void
    {
        $s = new Application('My app', '19.2');
        $s->setLogo('App Logo');

        $this->assertInstanceOf(Application::class, $s);
        $this->assertEquals('My app', $s->getName());
        $this->assertEquals('19.2', $s->getVersion());
        $this->assertEquals('App Logo', $s->getLogo());
        $this->assertCount(0, $s->getCommands());
        $this->assertCount(0, $s->argv());
    }

    public function testCustomExitHandler(): void
    {
        $cb = function (int $code) {
        };

        $s = new Application('My app', '19.2', $cb);
        $this->assertEquals(
            $cb,
            $this->getPropertyValue(Application::class, $s, 'onExit')
        );
    }

    public function testAddCommand(): void
    {
        $s = new Application('My app', '19.2');

        $s->command('cmd', 'My cmd', 'i', true, true);

        $commands = $s->getCommands();

        $this->assertCount(1, $commands);
        $this->assertArrayHasKey('cmd', $commands);

        /** @var Command $cmd */
        $cmd = $commands['cmd'];
        $this->assertEquals('cmd', $cmd->getName());
        $this->assertEquals('My cmd', $cmd->getDescription());
        $this->assertEquals('19.2', $cmd->getVersion());
        $this->assertEquals('i', $cmd->getAlias());
    }

    public function testAddCommandDuplicate(): void
    {
        $s = new Application('My app', '19.2');

        $s->command('cmd', 'My cmd', 'i', true, true);

        $this->expectException(InvalidArgumentException::class);
        $s->command('cmd', 'My cmd', 'i', true, true);
    }

    public function testShowHelp(): void
    {
        $cb = function (int $code) {
        };

        $s = new Application('My app', '19.2', $cb);
        $s->setLogo('My Logo');

        $output = $this->vfsOutputStream->url();
        $color = new MyColor();
        $writer = new Writer($output, $color);

        $io = $this->getMockInstance(Interactor::class, [
            'writer' => $writer
        ]);

        $s->io($io);

        $s->showHelp();

        $stdout = $this->getOutputContent();

        $this->assertEquals('[My Logo]
[My app, version 19.2]

[Commands:]
[  (n/a)]
', $stdout);
    }

    public function testHandle(): void
    {
        $cb = function (int $code) {
        };

        $s = new Application('My app', '19.2', $cb);
        $output = $this->vfsOutputStream->url();
        $color = new MyColor();
        $writer = new Writer($output, $color);

        $io = $this->getMockInstance(Interactor::class, [
            'writer' => $writer
        ]);

        $s->io($io);

        $cmd = new MyCommand('cmd', 'My cmd', true);
        $cmd->setAlias('c');
        $s->addCommand($cmd, '', false);

        $s->handle(['test.php', 'cmd', '-o', '90']);

        $stdout = $this->getOutputContent();

        $this->assertEquals('[interact method][execute method]', $stdout);
    }

    public function testHandleUsingAlias(): void
    {
        $cb = function (int $code) {
        };

        $s = new Application('My app', '19.2', $cb);
        $output = $this->vfsOutputStream->url();
        $color = new MyColor();
        $writer = new Writer($output, $color);

        $io = $this->getMockInstance(Interactor::class, [
            'writer' => $writer
        ]);

        $s->io($io);

        $cmd = new MyCommand('cmd', 'My cmd', true);
        $cmd->setAlias('c');
        $s->addCommand($cmd, '', false);

        $s->handle(['test.php', 'c', '-o', '90']);

        $stdout = $this->getOutputContent();

        $this->assertEquals('[interact method][execute method]', $stdout);
    }

    public function testHandleRequiredOption(): void
    {
        $cb = function (int $code) {
        };

        $s = new Application('My app', '19.2', $cb);
        $output = $this->vfsOutputStream->url();
        $color = new MyColor();
        $writer = new Writer($output, $color);

        $io = $this->getMockInstance(Interactor::class, [
            'writer' => $writer
        ]);

        $s->io($io);

        $cmd = new MyCommand('cmd', 'My cmd', true);
        $cmd->setAlias('c');
        $cmd->addOption('-p|--port', 'Port', null, true);

        $s->addCommand($cmd, '', false);

        $s->handle(['test.php', 'c', '-p']);

        $stdout = $this->getOutputContent();

        $this->assertEquals('[Option "--port" is required]
', $stdout);
    }

    public function testHandleThrowException(): void
    {
        $cb = function (int $code) {
        };

        $s = new Application('My app', '19.2', $cb);
        $output = $this->vfsOutputStream->url();
        $color = new MyColor();
        $writer = new Writer($output, $color);

        $io = $this->getMockInstance(Interactor::class, [
            'writer' => $writer
        ]);

        $s->io($io);

        $cmd = new MyCommandExecuteThrowException('cmd', 'My cmd', true);
        $cmd->setAlias('c');

        $s->addCommand($cmd, '', false);

        $s->handle(['test.php', 'c', '-p']);

        $stdout = $this->getOutputContent();

        $this->assertEquals('{COLORS}{COLORS}', $stdout);
    }

    public function testHandleShowHelp(): void
    {
        $cb = function (int $code) {
        };

        $s = new Application('My app', '19.2', $cb);
        $output = $this->vfsOutputStream->url();
        $color = new MyColor();
        $writer = new Writer($output, $color);

        $io = $this->getMockInstance(Interactor::class, [
            'writer' => $writer
        ]);

        $s->io($io);

        $cmd = new MyCommand('cmd', 'My cmd', true);
        $cmd->setAlias('c');
        $s->addCommand($cmd, '', false);

        $s->handle(['test.php']);

        $stdout = $this->getOutputContent();

        $this->assertEquals('[My app, version 19.2]

[Commands:]
[cmd c    ][My cmd]

[Run `<command> --help` for specific help]
', $stdout);
    }

    public function testCommandNotFound(): void
    {
        $cb = function (int $code) {
        };

        $s = new Application('My app', '19.2', $cb);
        $output = $this->vfsOutputStream->url();
        $color = new MyColor();
        $writer = new Writer($output, $color);

        $io = $this->getMockInstance(Interactor::class, [
            'writer' => $writer
        ]);

        $s->io($io);

        $s->handle(['test.php', 'cmd', '-o', '90']);

        $stdout = $this->getOutputContent();

        $this->assertEquals('[Command "cmd" not found]
', $stdout);
    }

    /**
     * Return test output stream content
     * @return string
     */
    private function getOutputContent(): string
    {
        return $this->vfsOutputStream->getContent();
    }
}
