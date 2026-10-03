<?php
/**
 * @copyright 2017
 * @author Stefan "eFrane" Graupner <efrane@meanderingsoul.com>
 */

namespace Tests\Unit\Output;


use EFrane\ConsoleAdditions\Output\FlysystemFileOutput;
use League\Flysystem\Filesystem;
use Tests\TestCase;

class FlysystemFileOutputTest extends TestCase
{
    public function testWritesToAdapter(): void
    {
        $this->markTestSkipped('Flysystem integration is currently under reconsideration');
    }
}
