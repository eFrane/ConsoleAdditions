<?php
/**
 * @copyright 2017
 * @author Stefan "eFrane" Graupner <efrane@meanderingsoul.com>
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
    /**
     * @param string $filename
     * @return resource file stream pointer
     * @throws FileOutputException if the file can not be opened
     */
    public function loadFileStream(string $filename)
    {
        $stream = fopen($filename, $this->getFileOpenMode());

        if ($stream && is_resource($stream)) {
            $this->stream = $stream;
            return $stream;
        }

        throw FileOutputException::failedToOpenFileForWriting($filename);
    }

    /**
     * Convert the file write mode constant to an fopen mode
     *
     * @return string `fopen()`-conform write mode descriptor
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
