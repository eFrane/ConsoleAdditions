<?php
/**
 * @copyright 2017
 * @author Stefan "eFrane" Graupner <efrane@meanderingsoul.com>
 */

namespace Tests\Unit\Output;


use EFrane\ConsoleAdditions\Output\MultiplexedOutput;
use EFrane\ConsoleAdditions\Output\NativeFileOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Tests\TestCase;

class MultiplexedOutputTest extends TestCase
{
    const TESTFILENAME = 'testfile.log';

    public function tearDown(): void
    {
        if (file_exists(self::TESTFILENAME)) {
            unlink(self::TESTFILENAME);
        }
    }

    public function testCreateWithOneInterface(): void
    {
        $sut = new MultiplexedOutput([
            new NullOutput(),
        ]);

        $this->assertInstanceOf(MultiplexedOutput::class, $sut);
        $this->assertCount(1, $sut->getInterfaces());
    }

    public function testCreateWithMultipleInterfaces(): void
    {
        $sut = new MultiplexedOutput([
            new NativeFileOutput(self::TESTFILENAME),
            new ConsoleOutput()
        ]);

        $this->assertCount(2, $sut->getInterfaces());
    }

    public function testWritesToAllInstances(): void
    {
        $consoleOutputMock = $this->createMock(ConsoleOutput::class);
        $consoleOutputMock->expects($this->once())->method('write');

        $sut = new MultiplexedOutput([
            new NativeFileOutput(self::TESTFILENAME),
            $consoleOutputMock
        ]);

        $sut->write('message');

        $content = file_get_contents(self::TESTFILENAME);
        $this->assertEquals('message', $content);
    }

    public function testIsVerboseForAllVerbosities(): void
    {
        $verbosities = [
            OutputInterface::VERBOSITY_VERBOSE,
            OutputInterface::VERBOSITY_VERY_VERBOSE,
            OutputInterface::VERBOSITY_DEBUG
        ];

        $sut = new MultiplexedOutput([
            new NullOutput()
        ]);

        foreach ($verbosities as $verbosity) {
            $sut->setVerbosity($verbosity);
            $this->assertTrue($sut->isVerbose());
        }
    }
}
