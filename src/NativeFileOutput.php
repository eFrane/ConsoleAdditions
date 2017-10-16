<?php
/**
 * @copyright 2017
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 */

namespace EFrane\ConsoleAdditions;


use EFrane\ConsoleAdditions\Exception\FileOutputException;

class NativeFileOutput extends FileOutput
{

    public function loadFileStream($filename)
    {
        $fp = fopen($filename, $this->getFileOpenMode());
        if ($fp) {
            return $fp;
        }

        throw FileOutputException::failedToOpenFileForWriting($filename);
    }

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