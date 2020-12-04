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
    public function testWritesToAdapter()
    {
        if (class_exists('League\Flysystem\Adapter\NullAdapter')) {
            /* @var \PHPUnit_Framework_MockObject_MockObject|\League\Flysystem\Adapter\NullAdapter $adapter */
            $adapter = $this->createMock(\League\Flysystem\Adapter\NullAdapter::class);
            $adapter->expects($this->once())->method('write');
        }

        if (class_exists('League\Flysystem\InMemory\InMemoryFilesystemAdapter')) {
            /* @var \PHPUnit_Framework_MockObject_MockObject|\League\Flysystem\InMemory\InMemoryFilesystemAdapter $adapter */
            $adapter = $this->createMock(\League\Flysystem\InMemory\InMemoryFilesystemAdapter::class);
            $adapter->expects($this->once())->method('write');
        }

        $filesystem = new Filesystem($adapter);

        $sut = new FlysystemFileOutput($filesystem, 'dummyfile');
        $sut->write('message');
    }
}
