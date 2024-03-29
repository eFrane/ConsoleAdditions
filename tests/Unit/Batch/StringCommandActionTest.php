<?php
/**
 * @copyright 2019
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 */

namespace Tests\Unit\Batch;


use EFrane\ConsoleAdditions\Batch\StringCommandAction;
use EFrane\ConsoleAdditions\Exception\BatchException;
use Tests\TestCommand;

class StringCommandActionTest extends BatchTestCase
{
    public function testExecutesCommand(): void
    {
        $this->app->add(new TestCommand());

        $sut = new StringCommandAction('testCommand');
        $sut->setApplication($this->app);
        $sut->execute($this->output);

        $this->assertEquals('Hello Test', $this->getOutput());
    }

    public function testExecuteFailsWithoutApplication(): void
    {
        $this->expectException(BatchException::class);

        $sut = new StringCommandAction('testCommand');
        $sut->execute($this->output);
    }

    public function testStringifiesOutputsCommandStringWithApp(): void
    {
        $this->app->add(new TestCommand());

        $sut = new StringCommandAction('testCommand');
        $sut->setApplication($this->app);

        $this->assertEquals('testApp testCommand', (string)$sut);

        $sut = new StringCommandAction('testCommand Harry');
        $sut->setApplication($this->app);

        $this->assertEquals('testApp testCommand Harry', (string)$sut);
    }
}
