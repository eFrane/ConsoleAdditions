<?php
/**
 * @copyright 2017
 * @author Stefan "eFrane" Graupner <stefan.graupner@gmail.com>
 * @license MIT
 */

namespace EFrane\ConsoleAdditions;


/**
 * Interface FileOutputInterface
 *
 * Outputting to a file requires a valid stream context
 * which will be obtained by calling the `loadFileStream` method.
 *
 * Additionally, to different writing modes are available:
 *
 * `FileOutputInterface::WRITE_MODE_APPEND` will open a file
 * and append new output to it's end if the file already exists.
 *
 * `FileOutputInterface::WRITE_MODE_RESET` will truncate
 * existing file contents instead.
 *
 * @package EFrane\ConsoleAdditions
 */
interface FileOutputInterface
{
    /**
     * Append output to the end of the file
     */
    const WRITE_MODE_APPEND = 1024;

    /**
     * Empty the file before first output is written
     */
    const WRITE_MODE_RESET = 2048;

    /**
     * @param $filename
     * @return resource a stream context
     */
    public function loadFileStream($filename);
}