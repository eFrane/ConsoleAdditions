<?php
/**
 * @copyright 2017
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 */

namespace Tests\Unit;


use EFrane\ConsoleAdditions\Output\FlysystemFileOutput;
use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\Filesystem;
use Tests\TestCase;

class FlysystemFileOutputTest extends TestCase
{
    public function testWritesToAdapter()
    {
        /* @var \PHPUnit_Framework_MockObject_MockObject|NullAdapter $adapter */
        $adapter = $this->createMock(NullAdapter::class);
        $adapter->expects($this->once())->method('writeStream');

        $filesystem = new Filesystem($adapter);

        $sut = new FlysystemFileOutput($filesystem, 'dummyfile');
        $sut->write('message');
    }
}