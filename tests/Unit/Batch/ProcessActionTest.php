<?php
/**
 * @copyright 2019
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 */

namespace Tests\Unit\Batch;


use EFrane\ConsoleAdditions\Batch\ProcessAction;
use Symfony\Component\Process\Process;

class ProcessActionTest extends BatchTestCase
{
    public function testExecutes(): void
    {
        $cwd = getcwd();

        $sut = new ProcessAction(new Process(['pwd']));
        $sut->execute($this->output);

        $this->assertEquals($cwd, trim($this->getOutput()));
    }

    public function testStringifies(): void
    {
        $sut = new ProcessAction(new Process(['pwd']));
        $this->assertEquals("'pwd'", (string)$sut);
    }
}
