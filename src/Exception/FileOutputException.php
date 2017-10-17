<?php
/**
 * @copyright 2017
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 * @license MIT
 */

namespace EFrane\ConsoleAdditions\Exception;


class FileOutputException extends \RuntimeException
{
    public static function invalidWriteMode($writeMode)
    {
        return new self("The write mode '{$writeMode}' is not supported.");
    }

    public static function failedToOpenFileForWriting($filename)
    {
        return new self("Failed to open '{$filename}' for writing.");
    }
}