<?php
/**
 * @copyright 2017
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 */

namespace EFrane\ConsoleAdditions;


use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\StreamOutput;

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
        $stream = $this->loadFileStream($filename);

        parent::__construct($stream, $verbosity, $decorated, $formatter);
    }
}