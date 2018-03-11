<?php
/**
 * @copyright 2017
 * @author Stefan "eFrane" Graupner <efrane@meanderingsoul.com>
 * @license MIT
 */

namespace EFrane\ConsoleAdditions\Output;


use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * FileOutput
 *
 * Outputting to a file is based on Symfony's streaming output.
 * A typical use case would be to keep a log of what's being outputted
 * for later analysis. This can be achieved easily in conjunction with
 * the `MultiplexedOutput` like so:
 *
 * ```
 * // assuming $output is your current ConsoleOutput
 * $output = new MultiplexedOutput([
 *      $output,
 *      new NativeFileOutput('logfile_of_whats_happening.txt')
 * ]);
 * ```
 *
 * For further documentation on write modes and implementation
 * of concrete file outputs see `FileOutputInterface`.
 *
 * @package EFrane\ConsoleAdditions
 */
abstract class FileOutput extends StreamOutput implements FileOutputInterface
{
    /**
     * @var string filename to write to
     */
    protected $filename = '';

    /**
     * @var int write mode
     * @see FileOutputInterface
     */
    protected $writeMode = self::WRITE_MODE_APPEND;

    /**
     * FileOutput constructor.
     *
     * @param string                        $filename
     * @param bool|int                      $writeMode
     * @param bool|int|null                 $verbosity
     * @param bool|null                     $decorated
     * @param OutputFormatterInterface|null $formatter
     */
    public function __construct(
        $filename,
        $writeMode = self::WRITE_MODE_APPEND,
        $verbosity = self::VERBOSITY_NORMAL,
        $decorated = null,
        OutputFormatterInterface $formatter = null
    ) {
        $this->writeMode = $writeMode;
        $this->filename = $filename;

        $stream = $this->loadFileStream($filename);

        parent::__construct($stream, $verbosity, $decorated, $formatter);
    }
}