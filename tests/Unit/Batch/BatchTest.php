<?php
/**
 * @copyright 2018
 * @author Stefan "eFrane" Graupner <efrane@meanderingsoul.com>
 */

namespace Tests\Unit\Batch;


use EFrane\ConsoleAdditions\Batch\Batch;
use EFrane\ConsoleAdditions\Batch\InstanceCommandAction;
use EFrane\ConsoleAdditions\Batch\ProcessAction;
use EFrane\ConsoleAdditions\Batch\ShellAction;
use EFrane\ConsoleAdditions\Batch\StringCommandAction;
use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\Process;
use Tests\TestCommand;

class BatchTest extends BatchTestCase
{
    public function testAdd(): void
    {
        $sut = new Batch($this->app, $this->output);
        $sut->add('list');

        $this->assertEquals(1, count($sut->getActions()));

        if (PHP_MAJOR_VERSION >= 7 && PHP_MINOR_VERSION >= 3) {
            $this->assertIsArray($sut->getActions());
        }

        $sut->add('help');

        $this->assertEquals(
            [
                (new StringCommandAction('list'))->setApplication($this->app),
                (new StringCommandAction('help'))->setApplication($this->app),
            ],
            $sut->getActions()
        );
    }

    public function testRunOne(): void
    {
        $sut = new Batch($this->app, $this->output);

        try {
            $action = new StringCommandAction('list');
            $action->setApplication($this->app);


            $sut->runOne($action);
        } catch (\Exception $e) {
        }

        $expected = <<<HD
testApp

Usage:
  command [options] [arguments]

Options:
  -h, --help            Display help for the given command. When no command is given display help for the list command
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi|--no-ansi  Force (or disable --no-ansi) ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  completion  Dump the shell completion script
  help        Display help for a command
  list        List commands

HD;

        $this->assertEquals($expected, $this->getOutput());
    }

    public function testRun(): void
    {
        $sut = new Batch($this->app, $this->output);
        $sut->setActions(
            [
                new StringCommandAction('list'),
                new StringCommandAction('list'),
            ]
        );

        try {
            $sut->run();
        } catch (\Exception $e) {
        }

        $expected = <<<HD
testApp

Usage:
  command [options] [arguments]

Options:
  -h, --help            Display help for the given command. When no command is given display help for the list command
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi|--no-ansi  Force (or disable --no-ansi) ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  completion  Dump the shell completion script
  help        Display help for a command
  list        List commands
testApp

Usage:
  command [options] [arguments]

Options:
  -h, --help            Display help for the given command. When no command is given display help for the list command
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi|--no-ansi  Force (or disable --no-ansi) ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  completion  Dump the shell completion script
  help        Display help for a command
  list        List commands

HD;

        $this->assertEquals($expected, $this->getOutput());
    }

    public function testAddObject(): void
    {
        $this->app->add(new TestCommand());

        $sut = new Batch($this->app, $this->output);
        $sut->addCommandInstance($this->app->get('testCommand'), new ArrayInput([]));
        $this->assertEquals(1, count($sut->getActions()));
        $this->assertInstanceOf(InstanceCommandAction::class, $sut->getActions()[0]);

        $sut->run();

        $this->assertEquals('Hello Test', $this->getOutput());
    }

    public function testAddMessage(): void
    {
        $sut = new Batch($this->app, $this->output);
        $sut->addMessage('Foo');
        $sut->run();

        $this->assertEquals("Foo\n", $this->getOutput());
    }

    public function testRunCascadesCommandException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Testing exception cascading');

        $this->app->add(new TestCommand());

        $sut = new Batch($this->app, $this->output);
        $sut->add('testCommand --throw-exception');

        $sut->run();
    }

    public function testRunSilent(): void
    {
        $this->app->add(new TestCommand());

        $sut = new Batch($this->app, $this->output);
        $sut->add('testCommand --throw-exception');

        $sut->runSilent();

        $this->assertTrue($sut->hasException());
        $this->assertEquals('Testing exception cascading', $sut->getLastException()->getMessage());
    }

    public function testAddShellAddsProcess(): void
    {
        $sut = new Batch($this->app, $this->output);
        $sut->addShell(['echo', 'Hello', 'Shell']);

        $this->assertEquals(1, count($sut->getActions()));
        $this->assertInstanceOf(ProcessAction::class, $sut->getActions()[0]);
        $this->assertInstanceOf(Process::class, $sut->getActions()[0]->getProcess());
    }

    public function testAddShellCbAddsConfiguredProcess(): void
    {
        $sut = new Batch($this->app, $this->output);
        $sut->addShellCb(
            ['echo', 'Hello', 'Shell'],
            function (Process $process) {
                $process->setWorkingDirectory('this/is/a/directory');

                return $process;
            }
        );

        $this->assertEquals(1, count($sut->getActions()));
        $this->assertInstanceOf(ProcessAction::class, $sut->getActions()[0]);
        $this->assertInstanceOf(Process::class, $sut->getActions()[0]->getProcess());
        $this->assertEquals('this/is/a/directory', $sut->getActions()[0]->getProcess()->getWorkingDirectory());
    }

    public function testRunReturnsShellOutput(): void
    {
        $sut = new Batch($this->app, $this->output);
        $sut->addShell(['echo', 'this-is-output']);
        $returnCode = $sut->run();

        $this->assertEquals(0, $returnCode);
        $this->assertEquals("this-is-output\n", $this->getOutput());
    }

    public function testRunReturnsLatestReturnCode(): void
    {
        $sut = new Batch($this->app, $this->output);

        $sut->addShell(['test', '1', '-eq', '0']); # 1 != 0, returns 1
        $sut->addShell(['test', '1', '-eq', '1']); # 1 == 1, returns 0

        $returnCode = $sut->run();

        $this->assertEquals(0, $returnCode);
        $this->assertTrue($sut->atLeastOneActionFailed());
        $this->assertFalse($sut->allActionsSucceeded());
    }

    public function testReturnCodesAreCorrect(): void
    {
        $sut = new Batch($this->app, $this->output);

        $this->assertCount(0, $sut->getAllReturnCodes());

        $sut->runOne(new ShellAction(['echo', 'Hello']));

        $this->assertCount(0, $sut->getAllReturnCodes());

        $sut->addShell(['test', '1', '-eq', '0']); # 1 != 0, returns 1
        $sut->addShell(['test', '1', '-eq', '1']); # 1 == 1, returns 0

        $sut->run();

        $this->assertEquals([1, 0], $sut->getAllReturnCodes());
    }

    public function testToStringForCommandStrings(): void
    {
        $sut = new Batch($this->app, $this->output);

        $sut->add('info');
        $sut->add('help');
        $sut->add('info');

        $this->assertEquals("testApp info\ntestApp help\ntestApp info", strval($sut));
    }

    public function testToStringForCommandInstances(): void
    {
        $sut = new Batch($this->app, $this->output);

        $this->app->add(new TestCommand());
        $testCommand = $this->app->get('testCommand');
        $sut->addCommandInstance($testCommand, new ArrayInput([]));
        $sut->addCommandInstance($testCommand, new ArrayInput(['--throw-exception', 'FancyTestName']));

        $this->assertEquals("testApp testCommand\ntestApp testCommand --throw-exception FancyTestName", strval($sut));
    }

    public function testAddTransparentVSprintf(): void
    {
        $sut = new Batch($this->app, $this->output);

        $this->app->add(new TestCommand());

        $sut->add('%s', 'test');
        $this->assertCount(1, $sut->getActions());
        $this->assertEquals('testApp test', (string)$sut->getActions()[0]);
    }

    public function testDoesNotKeepCommandState(): void
    {
        $sut = new Batch($this->app, $this->output);

        $this->app->add(new TestCommand());

        $sut->add('testCommand Johnny');
        $sut->add('testCommand June');

        $sut->run();

        $this->assertEquals("Hello JohnnyHello June", $this->getOutput());
        $this->assertNotEquals($sut->getActions()[0], $sut->getActions()[1]);
    }

    public function testOutputsErrors(): void
    {
        $sut = new Batch($this->app, $this->output);

        $this->app->add(new TestCommand());

        $sut->add('testCommand --throw-exception Foo');

        $this->expectException(RuntimeException::class);

        $sut->run();

        $this->assertEquals('Hello FooOops.', $this->getOutput());
    }
}
