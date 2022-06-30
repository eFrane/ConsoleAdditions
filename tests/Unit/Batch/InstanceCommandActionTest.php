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
    public function testAcceptsInstance(): void
    {
        $command = new TestCommand();
        $sut = new InstanceCommandAction($command, new StringInput(''));
        $this->assertInstanceOf(InstanceCommandAction::class, $sut);
    }

    public function testExecutesCommand(): void
    {
        $sut = new InstanceCommandAction(new TestCommand(), new StringInput(''));
        $sut->execute($this->output);

        $this->assertEquals('Hello Test', $this->getOutput());
    }

    public function testPassesInputToCommand(): void
    {
        $sut = new InstanceCommandAction(new TestCommand(), new StringInput('Input'));
        $sut->execute($this->output);

        $this->assertEquals('Hello Input', $this->getOutput());
    }

    public function testDoesntCatchExceptions(): void
    {
        $this->expectException(RuntimeException::class);

        $sut = new InstanceCommandAction(new TestCommand(), new StringInput('--throw-exception'));
        $sut->execute($this->output);
    }

    public function testStringifiesWithoutArguments(): void
    {
        $sut = new InstanceCommandAction(new TestCommand(), new StringInput(''));
        $sut->setApplication($this->app);

        $this->assertEquals('testApp testCommand', (string)$sut);
    }

    public function testStringifiesWithArguments(): void
    {
        $sut = new InstanceCommandAction(new TestCommand(), new StringInput('World'));
        $sut->setApplication($this->app);

        $this->assertEquals('testApp testCommand World', (string)$sut);

        $sut = new InstanceCommandAction(new TestCommand(), new ArrayInput(['World']));
        $sut->setApplication($this->app);

        $this->assertEquals('testApp testCommand World', (string)$sut);
    }

    public function testCanBeInstantiatedWithoutInput(): void
    {
        $sut = new InstanceCommandAction(new TestCommand());
        $this->assertInstanceOf(InstanceCommandAction::class, $sut);

        $sut->execute($this->output);
        $this->assertEquals('Hello Test', $this->getOutput());
    }
}
