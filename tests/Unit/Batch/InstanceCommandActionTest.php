<?php
/**
 * @copyright 2019
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 */

namespace Tests\Unit\Batch;


use EFrane\ConsoleAdditions\Batch\InstanceCommandAction;
use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\StringInput;
use Tests\TestCommand;

class InstanceCommandActionTest extends BatchTestCase
{
    public function testAcceptsInstance()
    {
        $command = new TestCommand();
        $sut = new InstanceCommandAction($command, new StringInput(''));
        $this->assertInstanceOf(InstanceCommandAction::class, $sut);
    }

    public function testExecutesCommand()
    {
        $sut = new InstanceCommandAction(new TestCommand(), new StringInput(''));
        $sut->execute($this->output);

        $this->assertEquals('Hello Test', $this->getOutput());
    }

    public function testPassesInputToCommand()
    {
        $sut = new InstanceCommandAction(new TestCommand(), new StringInput('Input'));
        $sut->execute($this->output);

        $this->assertEquals('Hello Input', $this->getOutput());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testDoesntCatchExceptions()
    {
        $sut = new InstanceCommandAction(new TestCommand(), new StringInput('--throw-exception'));
        $sut->execute($this->output);
    }

    public function testStringifiesWithoutArguments()
    {
        $sut = new InstanceCommandAction(new TestCommand(), new StringInput(''));
        $sut->setApplication($this->app);

        $this->assertEquals('testApp testCommand', (string)$sut);
    }

    public function testStringifiesWithArguments()
    {
        $sut = new InstanceCommandAction(new TestCommand(), new StringInput('World'));
        $sut->setApplication($this->app);

        $this->assertEquals('testApp testCommand World', (string)$sut);

        $sut = new InstanceCommandAction(new TestCommand(), new ArrayInput(['World']));
        $sut->setApplication($this->app);

        $this->assertEquals('testApp testCommand World', (string)$sut);
    }
}
