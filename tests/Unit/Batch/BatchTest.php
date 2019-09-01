<?php
/**
 * @copyright 2018
 * @author Stefan "eFrane" Graupner <efrane@meanderingsoul.com>
 */

namespace Tests\Unit\Batch;


use EFrane\ConsoleAdditions\Batch\Batch;
use EFrane\ConsoleAdditions\Batch\InstanceCommandAction;
use EFrane\ConsoleAdditions\Batch\ProcessAction;
use EFrane\ConsoleAdditions\Batch\StringCommandAction;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\Process;
use Tests\TestCommand;

class BatchTest extends BatchTestCase
{
    public function testAdd()
    {
        $sut = new Batch($this->app, $this->output);
        $sut->add('list');

        $this->assertEquals(1, count($sut->getActions()));
        $this->assertInternalType('array', $sut->getActions());
        $sut->add('help');

        $this->assertEquals(
            [
                (new StringCommandAction('list'))->setApplication($this->app),
                (new StringCommandAction('help'))->setApplication($this->app),
            ],
            $sut->getActions()
        );
    }

    public function testRunOne()
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
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  help  Displays help for a command
  list  Lists commands

HD;

        $this->assertEquals($expected, $this->getOutput());
    }

    public function testRun()
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
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  help  Displays help for a command
  list  Lists commands
testApp

Usage:
  command [options] [arguments]

Options:
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  help  Displays help for a command
  list  Lists commands

HD;

        $this->assertEquals($expected, $this->getOutput());
    }

    public function testAddObject()
    {
        $this->app->add(new TestCommand());

        $sut = new Batch($this->app, $this->output);
        $sut->addCommandInstance($this->app->get('testCommand'), new ArrayInput([]));
        $this->assertEquals(1, count($sut->getActions()));
        $this->assertInstanceOf(InstanceCommandAction::class, $sut->getActions()[0]);

        $sut->run();

        $this->assertEquals('Hello Test', $this->getOutput());
    }

    public function testAddMessage()
    {
        $sut = new Batch($this->app, $this->output);
        $sut->addMessage('Foo');
        $sut->run();

        $this->assertEquals("Foo\n", $this->getOutput());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Testing exception cascading
     * @throws \Exception
     */
    public function testRunCascadesCommandException()
    {
        $this->app->add(new TestCommand());

        $sut = new Batch($this->app, $this->output);
        $sut->add('testCommand --throw-exception');

        $sut->run();
    }

    public function testRunSilent()
    {
        $this->app->add(new TestCommand());

        $sut = new Batch($this->app, $this->output);
        $sut->add('testCommand --throw-exception');

        $sut->runSilent();

        $this->assertTrue($sut->hasException());
        $this->assertEquals('Testing exception cascading', $sut->getLastException()->getMessage());
    }

    public function testAddShellAddsProcess()
    {
        $sut = new Batch($this->app, $this->output);
        $sut->addShell('echo "Hello Shell"');

        $this->assertEquals(1, count($sut->getActions()));
        $this->assertInstanceOf(ProcessAction::class, $sut->getActions()[0]);
        $this->assertInstanceOf(Process::class, $sut->getActions()[0]->getProcess());
    }

    public function testAddShellCbAddsConfiguredProcess()
    {
        $sut = new Batch($this->app, $this->output);
        $sut->addShellCb(
            'echo "Hello Shell"',
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

    public function testRunReturnsShellOutput()
    {
        $sut = new Batch($this->app, $this->output);
        $sut->addShell('echo "My\nShell\nCommand"');
        $sut->run();

        $this->assertEquals("My\nShell\nCommand\n", $this->getOutput());
    }

    public function testToStringForCommandStrings()
    {
        $sut = new Batch($this->app, $this->output);

        $sut->add('info');
        $sut->add('help');
        $sut->add('info');

        $this->assertEquals("testApp info\ntestApp help\ntestApp info", strval($sut));
    }

    public function testToStringForCommandInstances()
    {
        $sut = new Batch($this->app, $this->output);

        $this->app->add(new TestCommand());
        $testCommand = $this->app->get('testCommand');
        $sut->addCommandInstance($testCommand, new ArrayInput([]));
        $sut->addCommandInstance($testCommand, new ArrayInput(['--throw-exception', 'FancyTestName']));

        $this->assertEquals("testApp testCommand\ntestApp testCommand --throw-exception FancyTestName", strval($sut));
    }

    public function testAddTransparentVSprintf()
    {
        $sut = new Batch($this->app, $this->output);

        $this->app->add(new TestCommand());

        $sut->add('%s', 'test');
        $this->assertCount(1, $sut->getActions());
        $this->assertEquals('testApp test', (string)$sut->getActions()[0]);
    }

    public function testDoesNotKeepCommandState()
    {
        $sut = new Batch($this->app, $this->output);

        $this->app->add(new TestCommand());

        $sut->add('testCommand Johnny');
        $sut->add('testCommand June');

        $sut->run();

        $this->assertEquals("Hello JohnnyHello June", $this->getOutput());
        $this->assertNotEquals($sut->getActions()[0], $sut->getActions()[1]);
    }
}
