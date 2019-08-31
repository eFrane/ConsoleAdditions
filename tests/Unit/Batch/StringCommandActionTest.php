<?php
/**
 * @copyright 2019
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 */

namespace Tests\Unit\Batch;


use EFrane\ConsoleAdditions\Batch\StringCommandAction;
use Tests\TestCommand;

class StringCommandActionTest extends BatchTestCase
{
    public function testExecutesCommand()
    {
        $this->app->add(new TestCommand());

        $sut = new StringCommandAction($this->app, 'testCommand');
        $sut->execute($this->output);

        $this->assertEquals('Hello Test', $this->getOutput());
    }

    public function testStringifiesOutputsCommandStringWithApp()
    {
        $this->app->add(new TestCommand());

        $sut = new StringCommandAction($this->app, 'testCommand');
        $this->assertEquals('testApp testCommand', (string)$sut);

        $sut = new StringCommandAction($this->app, 'testCommand Harry');
        $this->assertEquals('testApp testCommand Harry', (string)$sut);
    }
}
