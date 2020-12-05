<?php
/**
 * @copyright 2017
 * @author Stefan "eFrane" Graupner <efrane@meanderingsoul.com>
 * @license MIT
 */

namespace EFrane\ConsoleAdditions\Output;


use EFrane\ConsoleAdditions\Exception\FileOutputException;
use Exception;
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
     */
    public function __construct(
        Filesystem $filesystem,
        string $filename,
        int $writeMode = self::WRITE_MODE_APPEND,
        int $verbosity = self::VERBOSITY_NORMAL,
        bool $decorated = null,
        OutputFormatterInterface $formatter = null
    ) {
        $this->filesystem = $filesystem;

        $this->writeCallback = function ($message, $newline) {
            try {
                if ($newline) {
                    $message .= "\n";
                }

                $this->filesystem->write($this->filename, $message);
            } catch (Exception $e) {
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
        $stream = tmpfile();

        if (!$stream || !is_resource($stream)) {
            throw FileOutputException::failedToOpenFileForWriting($filename);
        }

        $this->stream = $stream;

        return $this->stream;
    }
}
