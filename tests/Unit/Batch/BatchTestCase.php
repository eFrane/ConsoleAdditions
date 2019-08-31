<?php
/**
 * @copyright 2017
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 * @license MIT
 */

namespace Tests\Unit\Batch;

use EFrane\ConsoleAdditions\Output\FileOutput;
use EFrane\ConsoleAdditions\Output\NativeFileOutput;
use Symfony\Component\Console\Application;

/**
 * Base test class
 */
abstract class BatchTestCase extends \PHPUnit\Framework\TestCase
{
    const TEST_OUTPUT_FILENAME = 'testfile.log';

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var FileOutput
     */
    protected $output;

    public function setUp()
    {
        $this->app = new Application('testApp');

        $this->output = new NativeFileOutput(self::TEST_OUTPUT_FILENAME, FileOutput::WRITE_MODE_RESET);
    }

    public function tearDown()
    {
        if (file_exists(self::TEST_OUTPUT_FILENAME)) {
            unlink(self::TEST_OUTPUT_FILENAME);
        }
    }

    protected function getOutput()
    {
        return file_get_contents(self::TEST_OUTPUT_FILENAME);
    }
}
