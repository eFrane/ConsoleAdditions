<?php
/**
 * @copyright 2017
 * @author Stefan "eFrane" Graupner <efrane@meanderingsoul.com>
 */

namespace Tests\Unit;

use EFrane\ConsoleAdditions\Output\NativeFileOutput;
use Tests\TestCase;

class NativeFileOutputTest extends TestCase
{
    const TESTFILENAME = 'testfile.log';

    public function tearDown()
    {
        if (file_exists(self::TESTFILENAME)) {
            unlink(self::TESTFILENAME);
        }
    }

    public function testUsesCorrectWriteMode()
    {
        $sut = new NativeFileOutput(self::TESTFILENAME);
        $this->assertEquals('a+', $sut->getFileOpenMode());
    }

    public function testWritesOutputToFile()
    {
        $sut = new NativeFileOutput(self::TESTFILENAME);

        $sut->write('message');

        $content = file_get_contents(self::TESTFILENAME);
        $this->assertEquals('message', $content);
    }

    /**
     * @depends testWritesOutputToFile
     */
    public function testAppendsOutputByDefault()
    {
        $sut = new NativeFileOutput(self::TESTFILENAME);
        $sut->writeln('message1');

        $sut = new NativeFileOutput(self::TESTFILENAME);
        $sut->writeln('message2');

        $lines = explode("\n", file_get_contents(self::TESTFILENAME));
        $expectedLines = [
            'message1',
            'message2',
            '',
        ];

        $this->assertCount(3, $lines);
        $this->assertEquals($expectedLines, $lines);
    }

    public function testResetsFileIfWriteModeIsReset()
    {
        $sut = new NativeFileOutput(self::TESTFILENAME);
        $sut->write('message1');

        $sut = new NativeFileOutput(self::TESTFILENAME, NativeFileOutput::WRITE_MODE_RESET);
        $sut->write('message2');

        $content = file_get_contents(self::TESTFILENAME);
        $this->assertEquals('message2', $content);
    }

    /**
     * @expectedException \EFrane\ConsoleAdditions\Exception\FileOutputException
     */
    public function testReportsIncorrectWriteMode()
    {
        new NativeFileOutput(self::TESTFILENAME, 512);
    }
}
