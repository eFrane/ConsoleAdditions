<?php
/**
 * @copyright 2017
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 * @license MIT
 */

namespace EFrane\ConsoleAdditions\Output;


use EFrane\ConsoleAdditions\Exception\FileOutputException;

/**
 * NativeFileOutput
 *
 * Creates an output stream to a file using the native
 * PHP file stream handling functions.
 *
 * @inheritdoc
 * @package EFrane\ConsoleAdditions
 */
class NativeFileOutput extends FileOutput
{
    protected $fp;

    /**
     * @param $filename
     * @return resource file stream pointer
     * @throws FileOutputException if the file can not be opened
     */
    public function loadFileStream($filename)
    {
        $this->fp = fopen($filename, $this->getFileOpenMode());
        if ($this->fp && is_resource($this->fp)) {
            return $this->fp;
        }

        throw FileOutputException::failedToOpenFileForWriting($filename);
    }

    /**
     * Closes open file resources on destruct
     */
    public function __destruct()
    {
        if (is_resource($this->fp)) {
            fclose($this->fp);
        }
    }

    /**
     * Convert the file write mode constant to an fopen mode
     *
     * @return string fopen()-conform write mode descriptor
     */
    public function getFileOpenMode()
    {
        switch ($this->writeMode) {
            case self::WRITE_MODE_APPEND:
                return 'a+';

            case self::WRITE_MODE_RESET:
                return 'w+';

            default:
                throw FileOutputException::invalidWriteMode($this->writeMode);
        }
    }
}