<?php
/**
 * @copyright 2018
 * @author Stefan "eFrane" Graupner <efrane@meanderingsoul.com>
 */

namespace Tests\Unit;


use EFrane\ConsoleAdditions\Command\Batch;
use EFrane\ConsoleAdditions\Output\FileOutput;
use EFrane\ConsoleAdditions\Output\NativeFileOutput;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tests\TestCase;

class BatchTest extends TestCase
{
    const TEST_OUTPUT_FILENAME = 'testfile.log';

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var FileOutput
     */
    protected $output;

    public function setUp()
    {
        $this->app = new Application();

        $this->output = new NativeFileOutput(self::TEST_OUTPUT_FILENAME, FileOutput::WRITE_MODE_RESET);
    }

    public function tearDown()
    {
        if (file_exists(self::TEST_OUTPUT_FILENAME)) {
            unlink(self::TEST_OUTPUT_FILENAME);
        }
    }

    public function testBatchAdd()
    {
        $sut = new Batch($this->app, $this->output);
        $sut->add('list');

        $this->assertEquals(1, count($sut->getCommands()));
        $this->assertInternalType('array', $sut->getCommands());
        $sut->add('help');

        $this->assertEquals([
            'list',
            'help',
        ], $sut->getCommands());
    }

    public function testBatchRunOne()
    {
        $sut = new Batch($this->app, $this->output);

        try {
            $sut->runOne('list');
        } catch (\Exception $e) {
        }

        $expected = <<<HD
Console Tool

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

    protected function getOutput()
    {
        return file_get_contents(self::TEST_OUTPUT_FILENAME);
    }

    public function testBatchRun()
    {
        $sut = new Batch($this->app, $this->output);
        $sut->setCommands([
            'list',
            'list',
        ]);

        try {
            $sut->run();
        } catch (\Exception $e) {
        }

        $expected = <<<HD
Console Tool

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
Console Tool

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
        $sut->addObject($this->app->get('test'), new ArrayInput([]));
        $this->assertEquals(1, count($sut->getCommands()));
        $this->assertInternalType('array', $sut->getCommands()[0]);

        // try {
            $sut->run();
        // } catch (\Exception $e) {
        // }

        $this->assertEquals('Hello Test', $this->getOutput());
    }

    /**
     * @expectedException \EFrane\ConsoleAdditions\Exception\BatchException
     */
    public function testAddThrowsOnObject()
    {
        $this->app->add(new TestCommand());

        $sut = new Batch($this->app, $this->output);
        $sut->add($this->app->get('test'));
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
        $sut->add('test --throw-exception');

        $sut->run();
    }

    /**
     * @expectedException \EFrane\ConsoleAdditions\Exception\BatchException
     */
    public function testRunOneThrowsOnInvalidArray()
    {
        $this->app->add(new TestCommand());

        $sut = new Batch($this->app, $this->output);
        $sut->runOne(['invalid' => 41, 'key' => 22]);
    }
}

final class TestCommand extends Command
{
    public function configure()
    {
        $this->setName('test');
        $this->addOption('throw-exception');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Hello Test');

        if ($input->getOption('throw-exception')) {
            throw new \RuntimeException("Testing exception cascading");
        }

        return 0;
    }
}