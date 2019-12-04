<?php
/**
 * @copyright 2017
 * @author Stefan "eFrane" Graupner <efrane@meanderingsoul.com>
 * @license MIT
 */

namespace EFrane\ConsoleAdditions\Exception;


class FileOutputException extends \RuntimeException
{
    public static function invalidWriteMode(int $writeMode): self
    {
        return new self("The write mode '{$writeMode}' is not supported.");
    }

    public static function failedToOpenFileForWriting(string $filename): self
    {
        return new self("Failed to open '{$filename}' for writing.");
    }
}
