<?php
/**
 * @copyright 2017
 * @author Stefan "eFrane" Graupner <efrane@meanderingsoul.com>
 * @license MIT
 */

namespace EFrane\ConsoleAdditions\Output;


use EFrane\ConsoleAdditions\Exception\FileOutputException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;

/**
 * Class FlysystemFileOutput
 *
 * Use Flysystem Adapters for file access
 *
 * Requires `tmpfile()`.
 *
 * @package EFrane\ConsoleAdditions
 */
class FlysystemFileOutput extends FileOutput
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * FlysystemFileOutput constructor.
     *
     * @param Filesystem                    $filesystem
     * @param bool|int                      $filename
     * @param bool|int|null                 $writeMode
     * @param bool|int|null                 $verbosity
     * @param null                          $decorated
     * @param OutputFormatterInterface|null $formatter
     */
    public function __construct(
        Filesystem $filesystem,
        $filename,
        $writeMode = self::WRITE_MODE_APPEND,
        $verbosity = self::VERBOSITY_NORMAL,
        $decorated = null,
        OutputFormatterInterface $formatter = null
    ) {
        $this->filesystem = $filesystem;

        $this->writeCallback = function ($message, $newline) {
            try {
                if ($newline) {
                    $message .= "\n";
                }

                $this->filesystem->write($this->filename, $message);
            } catch (FileNotFoundException $e) {
                throw FileOutputException::failedToOpenFileForWriting($this->filename);
            }
        };

        parent::__construct($filename, $writeMode, $verbosity, $decorated, $formatter);
    }

    /**
     * @param string $filename
     * @return resource
     */
    public function loadFileStream(string $filename)
    {
        $this->stream = tmpfile();

        if (!$this->stream || !is_resource($this->stream)) {
            throw FileOutputException::failedToOpenFileForWriting($filename);
        }

        return $this->stream;
    }
}
