<?php
/**
 * @copyright 2019
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 */

namespace Tests\Unit\Batch;


use EFrane\ConsoleAdditions\Batch\ShellAction;

class ShellActionTest extends BatchTestCase
{
    public function testExecutes()
    {
        $cwd = getcwd();

        $sut = new ShellAction(['pwd']);
        $sut->execute($this->output);

        $this->assertEquals($cwd, trim($this->getOutput()));
    }
}
