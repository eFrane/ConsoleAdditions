<?php
/**
 * @copyright 2017
 * @author Stefan "eFrane" Graupner <efrane@meanderingsoul.com>
 */

namespace Tests\Unit\Output;

use EFrane\ConsoleAdditions\Exception\FileOutputException;
use EFrane\ConsoleAdditions\Output\NativeFileOutput;
use Tests\TestCase;

class NativeFileOutputTest extends TestCase
{
    const TESTFILENAME = 'testfile.log';

    public function tearDown(): void
    {
        if (file_exists(self::TESTFILENAME)) {
            unlink(self::TESTFILENAME);
        }
    }

    public function testUsesCorrectWriteMode(): void
    {
        $sut = new NativeFileOutput(self::TESTFILENAME);
        $this->assertEquals('a+', $sut->getFileOpenMode());
    }

    public function testWritesOutputToFile(): void
    {
        $sut = new NativeFileOutput(self::TESTFILENAME);

        $sut->write('message');

        $content = file_get_contents(self::TESTFILENAME);
        $this->assertEquals('message', $content);
    }

    /**
     * @depends testWritesOutputToFile
     */
    public function testAppendsOutputByDefault(): void
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

    public function testResetsFileIfWriteModeIsReset(): void
    {
        $sut = new NativeFileOutput(self::TESTFILENAME);
        $sut->write('message1');

        $sut = new NativeFileOutput(self::TESTFILENAME, NativeFileOutput::WRITE_MODE_RESET);
        $sut->write('message2');

        $content = file_get_contents(self::TESTFILENAME);
        $this->assertEquals('message2', $content);
    }

    public function testReportsIncorrectWriteMode(): void
    {
        $this->expectException(FileOutputException::class);

        new NativeFileOutput(self::TESTFILENAME, 512);
    }

    public function testDebounces(): void
    {
        $sut = new NativeFileOutput(self::TESTFILENAME);
        $sut->setDebounceMilliseconds(1000);

        $sut->write('message');
        $content = file_get_contents(self::TESTFILENAME);
        $this->assertEquals('', $content);

        sleep(1);

        $sut->write('');

        $content = file_get_contents(self::TESTFILENAME);
        $this->assertEquals('message', $content);
    }
}
